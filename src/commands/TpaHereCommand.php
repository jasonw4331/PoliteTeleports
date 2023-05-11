<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\commands;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

class TpaHereCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $plugin){
		$this->setOwningPlugin($plugin);
		parent::__construct(
			"tpaskhere",
			CustomKnownTranslationFactory::command_tpahere_description(),
			CustomKnownTranslationFactory::command_tpahere_usage(),
			["tpah", "tpahere"]
		);
		$this->setPermission('PoliteTeleports.command.tpahere');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($args[0])){
			throw new InvalidCommandSyntaxException();
		}
		$player = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return;
		}
		$this->plugin->addRequest($player->getName(), $sender->getName(), $sender->getName());
		$sender->sendMessage(
			$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpahere_success($player->getName()))
		);
	}
}
