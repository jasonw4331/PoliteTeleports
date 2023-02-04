<?php
declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports\commands;

use jasonwynn10\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonwynn10\PoliteTeleports\Main;
use jasonwynn10\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\utils\TextFormat;

class TpDenyCommand extends Command implements PluginOwned{
	use PluginOwnedTrait{
		__construct as setOwningPlugin;
	}

	public function __construct(private Main $owningPlugin) {
		$this->setOwningPlugin($owningPlugin);
		parent::__construct(
			"tpdeny",
			CustomKnownTranslationFactory::command_tpdeny_description(),
			CustomKnownTranslationFactory::command_tpdeny_usage(),
			["tpno", "tpdeny", "tpd", "tpcancel"]
		);
		$this->setPermission('PoliteTeleports.command.tpdeny');
	}

	/**
	 * @inheritDoc
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)) {
			return;
		}
		if(!isset($this->owningPlugin->getActiveRequests()[$sender->getName()]) or count($this->owningPlugin->getActiveRequests()[$sender->getName()]) === 0) {
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpdeny_norequest());
			return;
		}
		if(isset($args[0])) {
			$target = $this->owningPlugin->getServer()->getPlayerByPrefix($args[0]);
			if($target === null) {
				$sender->sendMessage(CustomKnownTranslationFactory::command_tpdeny_noplayer($args[0])->prefix(TextFormat::RED));
				return;
			}
			foreach($this->owningPlugin->getActiveRequests()[$sender->getName()] as $request) {
				if($request->getFromTarget() === $target->getName() or $request->getToTarget() === $target->getName()) {
					$request->cancel();
					$sender->sendMessage(CustomKnownTranslationFactory::command_tpdeny_success($target->getName()));
					return;
				}
			}
			$sender->sendMessage(CustomKnownTranslationFactory::command_tpdeny_norequestplayer($target->getName())->prefix(TextFormat::RED));
			return;
		}
		/** @var TeleportRequest $request */
		$request = array_pop($this->owningPlugin->getActiveRequests()[$sender->getName()]);
		$request->cancel();
		$sender->sendMessage(CustomKnownTranslationFactory::command_tpdeny_success($request->getRequester()));
	}
}