<?php

declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonwynn10\PoliteTeleports\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
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

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($args[0])){
			throw new InvalidCommandSyntaxException();
		}
		$player = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
		if($player === null){
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpahere_noplayer($args[0])->prefix(TextFormat::RED));
			return;
		}
		$this->plugin->addRequest($player->getName(), $sender->getName(), $sender->getName());
		$sender->sendMessage(CustomKnownTranslationFactory::command_tpahere_success($player->getName()));
	}
}
