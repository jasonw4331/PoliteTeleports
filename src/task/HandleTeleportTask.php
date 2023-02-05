<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\task;

use jasonwynn10\PoliteTeleports\Main;
use jasonwynn10\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HandleTeleportTask extends Task{
	private int $finalTick;

	public function __construct(private TeleportRequest &$request, int $delayTicks) {
		$this->finalTick = Server::getInstance()->getTick() + $delayTicks;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun() : void{
		$server = Server::getInstance();
		$requester = $this->request->getRequester();
		$fromTarget = $server->getPlayerExact($this->request->getFromTarget());
		if($fromTarget === null) { // player offline
			$server->getPlayerExact($requester)?->sendMessage("The teleporting player is no longer online");
			throw new CancelTaskException();
		}
		$toTarget = $server->getPlayerExact($this->request->getToTarget());
		if($toTarget === null) { // player offline
			$server->getPlayerExact($requester)?->sendMessage("The receiving player is no longer online");
			throw new CancelTaskException();
		}

		if($this->request->isCancelled()) { // player first approved tp then denied while waiting
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
				$fromTarget->sendMessage("Teleport denied.");

			if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
				$toTarget->sendMessage("Teleport denied.");
			throw new CancelTaskException();
		}

		$tickDiff = $this->finalTick - $server->getTick();
		if($tickDiff >= 20 and Main::getPlayerSettings($fromTarget->getName())['Teleport Countdown']) {
			$fromTarget->sendMessage("Teleporting in ".ceil($tickDiff / 20)." second(s)");
			return;
		}

		if(!$fromTarget->teleport($toTarget->getLocation())) { // likely tp event cancelled by other plugin
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
				$fromTarget->sendMessage("Teleport to ".$toTarget->getName()." failed. Retrying...");

			$this->finalTick += $server->getPluginManager()->getPlugin('PoliteTeleports')->getConfig()->get('Retry Interval', 5) * 20; // retry teleport in configured seconds
			return;
		}

		// inform admins of player teleport
		Command::broadcastCommandMessage(
			$server->getPlayerExact($requester),
			KnownTranslationFactory::commands_tp_success($fromTarget->getName(), $toTarget->getName())
		);

		if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
			$fromTarget->sendMessage("Teleported to ".$toTarget->getName());

		if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
			$toTarget->sendMessage($fromTarget->getName()." has teleported to you");

		$this->request->cancel(); // request can now be removed from the queue

		throw new CancelTaskException;
	}
}