<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\Main;
use jasonwynn10\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class TpAcceptCommand extends Command implements PluginOwned{
	use PluginOwnedTrait{
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $owningPlugin) {
		$this->setOwningPlugin($owningPlugin);
		parent::__construct(
			"tpaccept",
			"Accept a teleport request",
			"/tpaccept [player: target]",
			["tpyes", "tpallow", "tpy"]
		);
		$this->setPermission('PoliteTeleports.command.tpaccept');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)) {
			return;
		}
		if(!isset($this->owningPlugin->getActiveRequests()[$sender->getName()]) or count($this->owningPlugin->getActiveRequests()[$sender->getName()]) === 0) {
			$sender->sendMessage("You have no active teleport requests");
			return;
		}
		if(isset($args[0])) {
			$target = $this->owningPlugin->getServer()->getPlayerByPrefix($args[0]);
			if($target === null) {
				$sender->sendMessage(TextFormat::RED . "Can't find player " . $args[0]);
				return;
			}
			foreach($this->owningPlugin->getActiveRequests()[$sender->getName()] as $request) {
				if($request->getFromTarget() === $target->getName() or $request->getToTarget() === $target->getName()) {
					$fromTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getFromTarget());
					$toTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getToTarget());
					if($fromTarget === null or $toTarget === null) {
						$sender->sendMessage("The other player is no longer online");
						return;
					}
					$this->teleportToPlayer($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20);
					return;
				}
			}
			$sender->sendMessage("You have no active requests from ".$target->getName());
			return;
		}
		$requests = $this->owningPlugin->getActiveRequests()[$sender->getName()];
		$request = $requests[array_key_last($requests)];
		$fromTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getFromTarget());
		$toTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getToTarget());
		if($fromTarget === null or $toTarget === null) {
			$sender->sendMessage("The other player is no longer online");
			return;
		}
		$this->teleportToPlayer($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20);
	}

	private function teleportToPlayer(TeleportRequest &$request, int $delayTicks) : void {
		$finalTick = Server::getInstance()->getTick() + $delayTicks;
		$this->owningPlugin->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(\Closure::fromCallable(
				static function() use (&$request, &$finalTick) : void {
					$requester = $request->getRequester();
					$fromTarget = Server::getInstance()->getPlayerExact($request->getFromTarget());
					if($fromTarget === null) { // player offline
						Server::getInstance()->getPlayerExact($requester)?->sendMessage("The teleporting player is no longer online");
						throw new CancelTaskException();
					}
					$toTarget = Server::getInstance()->getPlayerExact($request->getToTarget());
					if($toTarget === null) { // player offline
						Server::getInstance()->getPlayerExact($requester)?->sendMessage("The receiving player is no longer online");
						throw new CancelTaskException();
					}

					if($request->isCancelled()) { // player first approved tp then denied while waiting
						if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
							$fromTarget->sendMessage("Teleport denied.");

						if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
							$fromTarget->sendMessage("Teleport denied.");
						throw new CancelTaskException();
					}

					$tickDiff = $finalTick - Server::getInstance()->getTick();
					if($tickDiff >= 20 and Main::getPlayerSettings($fromTarget->getName())['Teleport Countdown']) {
						$fromTarget->sendMessage("Teleporting in ".ceil($tickDiff / 20)." second(s)");
						return;
					}

					if(!$fromTarget->teleport($toTarget->getLocation())) { // likely tp event cancelled by other plugin
						if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
							$fromTarget->sendMessage("Teleport to ".$toTarget->getName()." failed. Retrying...");

						$finalTick += $this->owningPlugin->getConfig()->get('Retry Interval', 5) * 20; // retry teleport in configured seconds
						return;
					}

					// inform admins of player teleport
					Command::broadcastCommandMessage(
						Server::getInstance()->getPlayerExact($requester),
						KnownTranslationFactory::commands_tp_success($fromTarget->getName(), $toTarget->getName())
					);

					if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
						$fromTarget->sendMessage("Teleported to ".$toTarget->getName());

					if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
						$toTarget->sendMessage($fromTarget->getName()." has teleported to you");

					$request->cancel(); // request can now be removed from the queue

					throw new CancelTaskException;
				}
			)),
			20
		);
	}
}