<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\task;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use jasonw4331\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function ceil;

class HandleTeleportTask extends Task{
	private int $finalTick;
	private ?Vector3 $standingAt;
	private int $attempt = 0;

	public function __construct(private TeleportRequest $request, int $delayTicks){
		$this->finalTick = Server::getInstance()->getTick() + $delayTicks;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun() : void{
		$server = Server::getInstance();
		$config = $server->getPluginManager()->getPlugin('PoliteTeleports')->getConfig();
		$requester = $this->request->requester;
		$fromTarget = $server->getPlayerExact($this->request->fromTarget);
		if($fromTarget === null){ // player offline
			if(Main::getPlayerSettings($this->request->toTarget)['Alert Receiver'])
				$server->getPlayerExact($this->request->toTarget)?->sendMessage(CustomKnownTranslationFactory::teleport_state_tpingoffline());
			throw new CancelTaskException();
		}
		$toTarget = $server->getPlayerExact($this->request->toTarget);
		if($toTarget === null){ // player offline
			if(Main::getPlayerSettings($this->request->fromTarget)['Alert Receiver'])
				$server->getPlayerExact($this->request->fromTarget)?->sendMessage(CustomKnownTranslationFactory::teleport_state_rcvoffline());
			throw new CancelTaskException();
		}

		if($this->request->isCancelled()){ // player first approved tp then denied while waiting
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
				$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());

			if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
				$toTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());
			throw new CancelTaskException();
		}

		$this->standingAt ??= $fromTarget->getPosition()->asVector3();

		$tickDiff = $this->finalTick - $server->getTick();
		if($tickDiff >= 20 && Main::getPlayerSettings($fromTarget->getName())['Teleport Countdown']){
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_time(
				CustomKnownTranslationFactory::teleport_state_seconds()->prefix(ceil($tickDiff / 20) . ' ')
			));
			return;
		}

		if($config->get('Stand Still', true) && $this->standingAt->distance($fromTarget->getPosition()) < 2) {
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());
			throw new CancelTaskException();
		}

		if(!$fromTarget->teleport($toTarget->getLocation())){ // likely tp event cancelled by other plugin
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'] && $this->request->destination === null)
				$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_failed($toTarget->getName()));

			if($this->attempt === 2){ // 3 attempts to teleport
				$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_failedfinal());
				throw new CancelTaskException();
			}
			$this->attempt++;

			$retryInterval = $config->get('Retry Interval', -1);
			if($retryInterval < 1) {
				$this->attempt = 2; // prevent reuse of this task object
				throw new CancelTaskException();
			}

			$this->finalTick += $retryInterval * 20; // retry teleport in configured seconds
			return;
		}

		// inform admins of player teleport
		Command::broadcastCommandMessage(
			$server->getPlayerExact($requester),
			KnownTranslationFactory::commands_tp_success($fromTarget->getName(), $toTarget->getName())
		);

		if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_successfrom($toTarget->getName()));

		if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
			$toTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_successto($fromTarget->getName()));

		$this->request->cancel(); // request can now be removed from the queue

		throw new CancelTaskException();
	}
}
