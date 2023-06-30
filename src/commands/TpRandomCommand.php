<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\commands;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use jasonw4331\PoliteTeleports\task\HandleTeleportTask;
use jasonw4331\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class TpRandomCommand extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as setOwningPlugin;
	}

	public function __construct(private readonly Main $plugin){
		$this->setOwningPlugin($plugin);
		parent::__construct(
			"tprandom",
			CustomKnownTranslationFactory::command_tprandom_description(),
			CustomKnownTranslationFactory::command_tprandom_usage(),
			["tprand", "randomtp", "tpr", "rtp"]
		);
		$this->setPermission('PoliteTeleports.command.tprandom');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$sender instanceof Player) {
			throw new InvalidCommandSyntaxException();
		}
		$this->plugin->getScheduler()->scheduleRepeatingTask(new HandleTeleportTask(
			new TeleportRequest(
				$sender->getName(),
				Main::RANDOM_KEYNAME,
				$sender->getName()
			),
			Main::getPlayerSettings($sender->getName())['Teleport Delay'] * 20
		), 20);
		$sender->sendMessage(CustomKnownTranslationFactory::command_tprandom_success());
	}
}
