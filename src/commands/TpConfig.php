<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\commands;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_shift;
use function count;
use function filter_var;
use function in_array;
use function mb_strtolower;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOL;
use const FILTER_VALIDATE_INT;

final class TpConfig extends Command implements PluginOwned{
	use PluginOwnedTrait {
		__construct as private setOwningPlugin;
	}

	public function __construct(private Main $plugin){
		$this->setOwningPlugin($plugin);
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
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}

		$playerSettings = $this->plugin->getPlayerSettings($sender->getName());

		if(count($args) < 1){
			// print existing setting values
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_header()->prefix(TextFormat::GREEN))
			);

			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_display(
					'language',
					TextFormat::GREEN . $playerSettings["Language"]
				)->prefix(TextFormat::BLUE))
			);
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_language_description()->prefix(TextFormat::YELLOW))
			);

			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-delay',
					$playerSettings["Teleport Delay"] > 0 ?
						TextFormat::GREEN . $playerSettings["Teleport Delay"] :
						CustomKnownTranslationFactory::command_tpconfig_delay_off()->prefix(TextFormat::RED)
				)->prefix(TextFormat::BLUE))
			);
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_delay_description()->prefix(TextFormat::YELLOW))
			);

			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-countdown',
					TextFormat::GREEN . ($playerSettings["Teleport Countdown"] ? "true" : "false")
				)->prefix(TextFormat::BLUE))
			);
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_countdown_description()->prefix(TextFormat::YELLOW))
			);

			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_display(
					'tp-alert',
					TextFormat::GREEN . ($playerSettings["Alert Teleporting"] ? "true" : "false")
				)->prefix(TextFormat::BLUE))
			);
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_alert_tp_description()->prefix(TextFormat::YELLOW))
			);

			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_display(
					'rcv-alert',
					TextFormat::GREEN . ($playerSettings["Alert Receiver"] ? "true" : "false")
				)->prefix(TextFormat::BLUE))
			);
			$sender->sendMessage(
				$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_alert_rcv_description()->prefix(TextFormat::YELLOW))
			);

			return;
		}
		$option = array_shift($args);
		$input = array_shift($args);

		switch($option){
			case "language":
				if($input === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_novalue()->prefix(TextFormat::RED))
					);
					return;
				}
				if(!in_array($input, array_keys(Main::getLanguages()), true)){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_language_invalid()->prefix(TextFormat::RED))
					);
					return;
				}
				$playerSettings["Language"] = mb_strtolower($input);
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success('Language', mb_strtolower($input))->prefix(TextFormat::GREEN))
				);
				break;
			case "tp-delay":
				if($input === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_novalue()->prefix(TextFormat::RED))
					);
					return;
				}
				$value = filter_var($input, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
				if($value === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_intvalue()->prefix(TextFormat::RED))
					);
					return;
				}
				$playerSettings["Teleport Delay"] = (int) $value;
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
						'Teleport Delay',
						CustomKnownTranslationFactory::teleport_state_seconds()->prefix($value . ' ')
					)->prefix(TextFormat::GREEN))
				);
				break;
			case "tp-countdown":
				if($input === null){
					$playerSettings["Teleport Countdown"] = !$playerSettings["Teleport Countdown"];
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
							'Teleport Countdown',
							$playerSettings["Teleport Countdown"] ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
						)->prefix(TextFormat::GREEN))
					);
					break;
				}
				$value = filter_var($input, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
				if($value === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED))
					);
					return;
				}
				$playerSettings["Teleport Countdown"] = (bool) $value;
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
						'Teleport Countdown',
						$value ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
					)->prefix(TextFormat::GREEN))
				);
				break;
			case "tp-alert":
				if($input === null){
					$playerSettings["Alert Teleporting"] = !$playerSettings["Alert Teleporting"];
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
							'Teleport alert',
							$playerSettings["Alert Teleporting"] ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
						)->prefix(TextFormat::GREEN))
					);
					break;
				}
				$value = filter_var($input, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
				if($value === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED))
					);
					return;
				}
				$playerSettings["Alert Teleporting"] = (bool) $value;
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
						'Teleport alert',
						$value ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
					)->prefix(TextFormat::GREEN))
				);
				break;
			case "rcv-alert":
				if($input === null){
					$playerSettings["Alert Receiver"] = !$playerSettings["Alert Receiver"];
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
							'Receiver alert',
							$playerSettings["Alert Receiver"] ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
						)->prefix(TextFormat::GREEN))
					);
					break;
				}
				$value = filter_var($input, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
				if($value === null){
					$sender->sendMessage(
						$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_boolvalue()->prefix(TextFormat::RED))
					);
					return;
				}
				$playerSettings["Alert Receiver"] = (bool) $value;
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_success(
						'Receiver alert',
						$value ? TextFormat::GREEN . "true" : TextFormat::RED . "false"
					)->prefix(TextFormat::GREEN))
				);
				break;
			default:
				$sender->sendMessage(
					$sender->getLanguage()->translate(CustomKnownTranslationFactory::command_tpconfig_invalid()->prefix(TextFormat::RED))
				);
				return;
		}
		$this->plugin->updatePlayerSettings($sender->getName(), $playerSettings);
	}
}
