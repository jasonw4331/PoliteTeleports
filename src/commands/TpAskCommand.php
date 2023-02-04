<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

class TpAskCommand extends Command implements PluginOwned{
	use PluginOwnedTrait{
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $owningPlugin) {
		$this->setOwningPlugin($owningPlugin);
		parent::__construct(
			"tpask",
			"Send a request to teleport to another player",
			"/tpask <player: target>",
			["tpa"]
		);
		$this->setPermission('PoliteTeleports.command.tpask');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)) {
			return;
		}
		if(!isset($args[0])) {
			throw new InvalidCommandSyntaxException;
		}
		$player = $this->owningPlugin->getServer()->getPlayerByPrefix($args[0]);
		if($player === null) {
			$sender->sendMessage(TextFormat::RED . "Can't find player " . $args[0]);
			return;
		}
		$this->owningPlugin->addRequest($sender->getName(), $player->getName(), $sender->getName());
		$sender->sendMessage("Teleport request sent to ".$player->getName());
	}
}