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
use function array_key_last;
use function count;

class TpAcceptCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $plugin){
		$this->setOwningPlugin($plugin);
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
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($this->plugin->getActiveRequests()[$sender->getName()]) || count($this->plugin->getActiveRequests()[$sender->getName()]) === 0){
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_norequest());
			return;
		}
		if(isset($args[0])){
			$target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_noplayer($args[0])->prefix(TextFormat::RED));
				return;
			}
			foreach($this->plugin->getActiveRequests()[$sender->getName()] as $request){
				if($request->getFromTarget() === $target->getName() || $request->getToTarget() === $target->getName()){
					$fromTarget = $this->plugin->getServer()->getPlayerExact($request->getFromTarget());
					$toTarget = $this->plugin->getServer()->getPlayerExact($request->getToTarget());
					if($fromTarget === null || $toTarget === null){
						$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_offline());
						return;
					}
					$this->plugin->getScheduler()->scheduleRepeatingTask(new HandleTeleportTask($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20), 20);
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_success());
					return;
				}
			}
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_norequestplayer($target->getName()));
			return;
		}
		$requests = $this->plugin->getActiveRequests()[$sender->getName()];
		$request = $requests[array_key_last($requests)];
		$fromTarget = $this->plugin->getServer()->getPlayerExact($request->getFromTarget());
		$toTarget = $this->plugin->getServer()->getPlayerExact($request->getToTarget());
		if($fromTarget === null || $toTarget === null){
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_offline());
			return;
		}
		$this->plugin->getScheduler()->scheduleRepeatingTask(new HandleTeleportTask($request, Main::getPlayerSettings($request->getFromTarget())['Teleport Delay'] * 20), 20);
		$sender->sendMessage(CustomKnownTranslationFactory::command_tpaccept_success());
	}
}
