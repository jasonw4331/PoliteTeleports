<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

final class TpConfig extends Command implements PluginOwned{
	use PluginOwnedTrait{
		__construct as private setOwningPlugin;
	}

	public function __construct(private Main $owningPlugin) {
		$this->setOwningPlugin($owningPlugin);
		parent::__construct(
			"tpconfig",
			"Configure your teleportation experience",
			"/tpconfig [option: string] [value: string]",
			["tpc"]
		);
		$this->setPermission('PoliteTeleports.command.tpconfig');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)) {
			return;
		}

		$playerSettings = $this->owningPlugin->getPlayerSettings($sender->getName());

		if(count($args) < 1) {
			// print existing setting values
			$sender->sendMessage(TextFormat::GREEN . "Your current settings:");

			$sender->sendMessage(TextFormat::BLUE . "tp-delay: " . ($playerSettings["Teleport Delay"] > 0 ? TextFormat::GREEN.$playerSettings["Teleport Delay"] : TextFormat::RED."off"));
			$sender->sendMessage(TextFormat::YELLOW . "description: The number of seconds to wait before teleporting");

			$sender->sendMessage(TextFormat::BLUE . "tp-countdown: " . ($playerSettings["Teleport Countdown"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
			$sender->sendMessage(TextFormat::YELLOW . "description: Display a countdown in chat before teleporting");

			$sender->sendMessage(TextFormat::BLUE . "tp-alert: " . ($playerSettings["Alert Teleporting"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
			$sender->sendMessage(TextFormat::YELLOW . "description: Display a message when you are teleporting");

			$sender->sendMessage(TextFormat::BLUE . "rcv-alert: " . ($playerSettings["Alert Receiver"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
			$sender->sendMessage(TextFormat::YELLOW . "description: Display a message for the player you are teleporting to when you are teleporting");

			return;
		}
		$option = array_shift($args);
		$value = array_shift($args);

		switch($option){
			case "tp-delay":
				if($value === null){
					$sender->sendMessage(TextFormat::RED . "You must specify a value for this option");
					return;
				}
				if(!is_numeric($value)){
					$sender->sendMessage(TextFormat::RED . "The value must be a number");
					return;
				}
				$playerSettings["Teleport Delay"] = (int) $value;
				$sender->sendMessage(TextFormat::GREEN . "Teleport delay set to " . $value . " seconds");
				break;
			case "tp-countdown":
				if($value === null){
					$playerSettings["Teleport Countdown"] = !$playerSettings["Teleport Countdown"];
					$sender->sendMessage(TextFormat::GREEN . "Teleport countdown set to " . ($playerSettings["Teleport Countdown"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(TextFormat::RED . "The value must be a boolean");
					return;
				}
				$playerSettings["Teleport Countdown"] = (bool) $value;
				$sender->sendMessage(TextFormat::GREEN . "Teleport countdown set to " . ($value ? TextFormat::GREEN."true" : TextFormat::RED."false"));
				break;
			case "tp-alert":
				if($value === null){
					$playerSettings["Alert Teleporting"] = !$playerSettings["Alert Teleporting"];
					$sender->sendMessage(TextFormat::GREEN . "Teleport alert set to " . ($playerSettings["Alert Teleporting"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(TextFormat::RED . "The value must be a boolean");
					return;
				}
				$playerSettings["Alert Teleporting"] = (bool) $value;
				$sender->sendMessage(TextFormat::GREEN . "Teleport alert set to " . ($value ? TextFormat::GREEN."true" : TextFormat::RED."false"));
				break;
			case "rcv-alert":
				if($value === null){
					$playerSettings["Alert Receiver"] = !$playerSettings["Alert Receiver"];
					$sender->sendMessage(TextFormat::GREEN . "Receiver alert set to " . ($playerSettings["Alert Receiver"] ? TextFormat::GREEN."true" : TextFormat::RED."false"));
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(TextFormat::RED . "The value must be a boolean");
					return;
				}
				$playerSettings["Alert Receiver"] = (bool) $value;
				$sender->sendMessage(TextFormat::GREEN . "Receiver alert set to " . ($value ? TextFormat::GREEN."true" : TextFormat::RED."false"));
				break;
			default:
				$sender->sendMessage(TextFormat::RED . "Invalid setting");
				return;
		}
		$this->owningPlugin->updatePlayerSettings($sender->getName(), $playerSettings);
	}
}