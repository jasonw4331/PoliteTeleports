<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\task;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use jasonw4331\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function ceil;

class HandleTeleportTask extends Task{
	private int $finalTick;

	public function __construct(private TeleportRequest &$request, int $delayTicks){
		$this->finalTick = Server::getInstance()->getTick() + $delayTicks;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun() : void{
		$server = Server::getInstance();
		$requester = $this->request->getRequester();
		$fromTarget = $server->getPlayerExact($this->request->getFromTarget());
		if($fromTarget === null){ // player offline
			if(Main::getPlayerSettings($this->request->getToTarget())['Alert Receiver']){
				$target = $server->getPlayerExact($this->request->getToTarget());
				$target?->sendMessage(
					$target->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_tpingoffline())
				);
			}
			throw new CancelTaskException();
		}
		$toTarget = $server->getPlayerExact($this->request->getToTarget());
		if($toTarget === null){ // player offline
			if(Main::getPlayerSettings($this->request->getFromTarget())['Alert Receiver']){
				$fromTarget->sendMessage(
					$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_rcvoffline())
				);
			}
			throw new CancelTaskException();
		}

		if($this->request->isCancelled()){ // player first approved tp then denied while waiting
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
				$fromTarget->sendMessage(
					$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_cancelled())
				);

			if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
				$toTarget->sendMessage(
					$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_cancelled())
				);
			throw new CancelTaskException();
		}

		$tickDiff = $this->finalTick - $server->getTick();
		if($tickDiff >= 20 && Main::getPlayerSettings($fromTarget->getName())['Teleport Countdown']){
			$fromTarget->sendMessage($fromTarget->getLanguage()->translate(
				CustomKnownTranslationFactory::teleport_state_time(
					CustomKnownTranslationFactory::teleport_state_seconds()->prefix(ceil($tickDiff / 20) . ' ')
				)
			));
			return;
		}

		if(!$fromTarget->teleport($toTarget->getLocation())){ // likely tp event cancelled by other plugin
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
				$fromTarget->sendMessage(
					$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_failed($toTarget->getName()))
				);

			$this->finalTick += $server->getPluginManager()->getPlugin('PoliteTeleports')->getConfig()->get('Retry Interval', 5) * 20; // retry teleport in configured seconds
			return;
		}

		// inform admins of player teleport
		Command::broadcastCommandMessage(
			$server->getPlayerExact($requester),
			KnownTranslationFactory::commands_tp_success($fromTarget->getName(), $toTarget->getName())
		);

		if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
			$fromTarget->sendMessage(
				$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_successfrom($toTarget->getName()))
			);

		if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
			$toTarget->sendMessage(
				$fromTarget->getLanguage()->translate(CustomKnownTranslationFactory::teleport_state_successto($fromTarget->getName()))
			);

		$this->request->cancel(); // request can now be removed from the queue

		throw new CancelTaskException();
	}
}
