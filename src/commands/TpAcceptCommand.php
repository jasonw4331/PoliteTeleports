<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonwynn10\PoliteTeleports\Main;
use jasonwynn10\PoliteTeleports\task\HandleTeleportTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

class TpAcceptCommand extends Command implements PluginOwned{
	use PluginOwnedTrait{
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $owningPlugin) {
		$this->setOwningPlugin($owningPlugin);
		parent::__construct(
			"tpaccept",
			CustomKnownTranslationFactory::command_tpaccept_description(),
			CustomKnownTranslationFactory::command_tpaccept_usage(),
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
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_norequest());
			return;
		}
		if(isset($args[0])) {
			$target = $this->owningPlugin->getServer()->getPlayerByPrefix($args[0]);
			if($target === null) {
				$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_noplayer($args[0])->prefix(TextFormat::RED));
				return;
			}
			foreach($this->owningPlugin->getActiveRequests()[$sender->getName()] as $request) {
				if($request->getFromTarget() === $target->getName() or $request->getToTarget() === $target->getName()) {
					$fromTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getFromTarget());
					$toTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getToTarget());
					if($fromTarget === null or $toTarget === null) {
						$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_offline());
						return;
					}
					$this->owningPlugin->getScheduler()->scheduleRepeatingTask(new HandleTeleportTask($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20), 20);
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_success());
					return;
				}
			}
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_norequestplayer($target->getName()));
			return;
		}
		$requests = $this->owningPlugin->getActiveRequests()[$sender->getName()];
		$request = $requests[array_key_last($requests)];
		$fromTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getFromTarget());
		$toTarget = $this->owningPlugin->getServer()->getPlayerExact($request->getToTarget());
		if($fromTarget === null or $toTarget === null) {
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_offline());
			return;
		}
		$this->owningPlugin->getScheduler()->scheduleRepeatingTask(new HandleTeleportTask($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20), 20);
		$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_success());
	}
}