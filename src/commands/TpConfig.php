<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\lang\CustomKnownTranslationFactory;
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
			CustomKnownTranslationFactory::command_tpconfig_description(),
			CustomKnownTranslationFactory::command_tpconfig_usage(),
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
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_header()->prefix(TextFormat::GREEN));

			$sender->sendMessage(
				CustomKnownTranslationFactory::command_tpconfig_display(
					'language',
					TextFormat::GREEN . $playerSettings["Language"]
				)->prefix(TextFormat::BLUE)
			);
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_language_description()->prefix(TextFormat::YELLOW));

			$sender->sendMessage(
				CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-delay',
					TextFormat::GREEN . (
						$playerSettings["Teleport Delay"] > 0 ?
							$playerSettings["Teleport Delay"] :
							CustomKnownTranslationFactory::command_tpconfig_delay_off()
					)
				)->prefix(TextFormat::BLUE)
			);
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_delay_description()->prefix(TextFormat::YELLOW));

			$sender->sendMessage(
				CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-countdown',
					TextFormat::GREEN . ($playerSettings["Teleport Countdown"] ? "true" : "false")
				)->prefix(TextFormat::BLUE)
			);
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_countdown_description()->prefix(TextFormat::YELLOW));

			$sender->sendMessage(
				CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-alert',
					TextFormat::GREEN . ($playerSettings["Alert Teleporting"] ? "true" : "false")
				)->prefix(TextFormat::BLUE)
			);
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_alert_tp_description()->prefix(TextFormat::YELLOW));

			$sender->sendMessage(
				CustomKnownTranslationFactory::command_tpconfig_display(
					'rcv-alert',
					TextFormat::GREEN . ($playerSettings["Alert Receiver"] ? "true" : "false")
				)->prefix(TextFormat::BLUE)
			);
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_alert_rcv_description()->prefix(TextFormat::YELLOW));

			return;
		}
		$option = array_shift($args);
		$value = array_shift($args);

		switch($option){
			case "language":
				if($value === null){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_novalue()->prefix(TextFormat::RED));
					return;
				}
				if(!in_array($value, array_keys(Main::getLanguages()))){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_language_invalid()->prefix(TextFormat::RED));
					return;
				}
				$playerSettings["Language"] = mb_strtolower($value);
				$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_success('Language', mb_strtolower($value))->prefix(TextFormat::GREEN));
				break;
			case "tp-delay":
				if($value === null){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_novalue()->prefix(TextFormat::RED));
					return;
				}
				if(!is_numeric($value)){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_intvalue()->prefix(TextFormat::RED));
					return;
				}
				$playerSettings["Teleport Delay"] = (int) $value;
				$sender->sendMessage(
					CustomKnownTranslationFactory::command_tpconfig_success('Teleport Delay', $value)->format(
						TextFormat::GREEN,
						CustomKnownTranslationFactory::command_tpconfig_delay_seconds()->getText()
					)
				);
				break;
			case "tp-countdown":
				if($value === null){
					$playerSettings["Teleport Countdown"] = !$playerSettings["Teleport Countdown"];
					$sender->sendMessage(
						CustomKnownTranslationFactory::command_tpconfig_success(
							'Teleport Countdown',
							$playerSettings["Teleport Countdown"] ? TextFormat::GREEN."true" : TextFormat::RED."false"
						)->prefix(TextFormat::GREEN)
					);
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED));
					return;
				}
				$playerSettings["Teleport Countdown"] = (bool) $value;
				$sender->sendMessage(
					CustomKnownTranslationFactory::command_tpconfig_success(
						'Teleport Countdown',
						$value ? TextFormat::GREEN."true" : TextFormat::RED."false"
					)->prefix(TextFormat::GREEN)
				);
				break;
			case "tp-alert":
				if($value === null){
					$playerSettings["Alert Teleporting"] = !$playerSettings["Alert Teleporting"];
					$sender->sendMessage(
						CustomKnownTranslationFactory::command_tpconfig_success(
							'Teleport alert',
							$playerSettings["Alert Teleporting"] ? TextFormat::GREEN."true" : TextFormat::RED."false"
						)->prefix(TextFormat::GREEN)
					);
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED));
					return;
				}
				$playerSettings["Alert Teleporting"] = (bool) $value;
				$sender->sendMessage(
					CustomKnownTranslationFactory::command_tpconfig_success(
						'Teleport alert',
						$value ? TextFormat::GREEN."true" : TextFormat::RED."false"
					)->prefix(TextFormat::GREEN)
				);
				break;
			case "rcv-alert":
				if($value === null){
					$playerSettings["Alert Receiver"] = !$playerSettings["Alert Receiver"];
					$sender->sendMessage(
						CustomKnownTranslationFactory::command_tpconfig_success(
							'Receiver alert',
							$playerSettings["Alert Receiver"] ? TextFormat::GREEN."true" : TextFormat::RED."false"
						)->prefix(TextFormat::GREEN)
					);
					break;
				}
				if(!is_bool($value)){
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED));
					return;
				}
				$playerSettings["Alert Receiver"] = (bool) $value;
				$sender->sendMessage(
					CustomKnownTranslationFactory::command_tpconfig_success(
						'Receiver alert',
						$value ? TextFormat::GREEN."true" : TextFormat::RED."false"
					)->prefix(TextFormat::GREEN)
				);
				break;
			default:
				$sender->sendMessage(CustomKnownTranslationFactory::command_tpconfig_invalid()->prefix(TextFormat::RED));
				return;
		}
		$this->owningPlugin->updatePlayerSettings($sender->getName(), $playerSettings);
	}
}