<?php

declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonwynn10\PoliteTeleports\Main;
use jasonwynn10\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;
use function array_pop;
use function count;

class TpDenyCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $plugin){
		$this->setOwningPlugin($plugin);
		parent::__construct(
			"tpdeny",
			CustomKnownTranslationFactory::command_tpdeny_description(),
			CustomKnownTranslationFactory::command_tpdeny_usage(),
			["tpno", "tpd", "tpcancel"]
		);
		$this->setPermission('PoliteTeleports.command.tpdeny');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($this->plugin->getActiveRequests()[$sender->getName()]) || count($this->plugin->getActiveRequests()[$sender->getName()]) === 0){
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpdeny_norequest())
			);
			return;
		}
		if(isset($args[0])){
			$target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
				return;
			}
			foreach($this->plugin->getActiveRequests()[$sender->getName()] as $request){
				if($request->getFromTarget() === $target->getName() || $request->getToTarget() === $target->getName()){
					$request->cancel();
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpdeny_success($target->getName()))
					);
					return;
				}
			}
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpdeny_norequestplayer($target->getName())->prefix(TextFormat::RED))
			);
			return;
		}
		/** @var TeleportRequest $request */
		$request = array_pop($this->plugin->getActiveRequests()[$sender->getName()]);
		$request->cancel();
		$sender->sendMessage(
			$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpdeny_success($request->getRequester()))
		);
	}
}
