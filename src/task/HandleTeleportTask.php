<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports\task;

use jasonw4331\PoliteTeleports\lang\CustomKnownTranslationFactory;
use jasonw4331\PoliteTeleports\Main;
use jasonw4331\PoliteTeleports\TeleportRequest;
use pocketmine\command\Command;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use function abs;
use function ceil;
use function max;
use function min;
use function mt_rand;

class HandleTeleportTask extends Task{
	private int $finalTick;
	private ?Vector3 $standingAt;
	private ?Vector3 $randomVector;
	private int $attempt = 0;

	public function __construct(private readonly TeleportRequest $request, int $delayTicks){
		$this->finalTick = Server::getInstance()->getTick() + $delayTicks;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun() : void{
		$server = Server::getInstance();
		/** @phpstan-var Config $config */
		$config = $server->getPluginManager()->getPlugin('PoliteTeleports')?->getConfig() ?? throw new AssumptionFailedError('PoliteTeleports plugin not loaded');
		$fromTarget = $server->getPlayerExact($this->request->fromTarget);
		if($fromTarget === null){ // player offline
			if(Main::getPlayerSettings($this->request->toTarget)['Alert Receiver'])
				$server->getPlayerExact($this->request->toTarget)?->sendMessage(CustomKnownTranslationFactory::teleport_state_tpingoffline());
			throw new CancelTaskException();
		}

		$this->standingAt ??= $fromTarget->getPosition()->asVector3();

		if($config->get('Stand Still', true) === true && $this->standingAt->distance($fromTarget->getPosition()) < 2){
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());
			throw new CancelTaskException();
		}

		if($this->request->toTarget !== Main::RANDOM_KEYNAME){
			$toTarget = $server->getPlayerExact($this->request->toTarget);
			if($toTarget === null){ // player offline
				if(Main::getPlayerSettings($this->request->fromTarget)['Alert Receiver'])
					$server->getPlayerExact($this->request->fromTarget)?->sendMessage(CustomKnownTranslationFactory::teleport_state_rcvoffline());
				throw new CancelTaskException();
			}

			if($this->request->isCancelled()){ // player first approved tp then denied while waiting
				if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
					$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());

				if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'])
					$toTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_cancelled());
				throw new CancelTaskException();
			}
		}else{
			$radius = min(Main::getPlayerSettings($fromTarget->getName())['Random Location Radius'], 2 ** 21); // hard cap teleport distance at around 2 million blocks
			/** @var array{int, int, int} $currentCoords */
			$currentCoords = [];
			for($i = 0; $i < 3; ++$i){
				$value = $currentCoords[$i] = mt_rand(-$radius, $radius);
				if(abs($value) < Server::getInstance()->getAllowedViewDistance($fromTarget->getViewDistance()) * Chunk::EDGE_LENGTH){ // always teleport the player outside render distance
					$i--;
				}
			}
			$this->randomVector = Position::fromObject(
				$fromTarget->getPosition()
					->add($currentCoords[0], $currentCoords[1], $currentCoords[2])
					->withComponents(null, min(max($currentCoords[1], World::Y_MIN), World::Y_MAX), null),
				$fromTarget->getWorld()
			);
			if(Main::getPlayerSettings($fromTarget->getName())['Random Location Safety'] === true){
				$fromTarget->getWorld()->requestSafeSpawn($this->randomVector)->onCompletion(
					fn(Position $coords) => $this->randomVector = $coords,
					fn() => $this->getHandler()?->cancel()
				);
			}
			$toTarget = new class($this->randomVector){
				public function __construct(private readonly Position $location){ }

				public function getLocation() : Position{
					return $this->location;
				}

				public function getName() : string{
					return "{$this->location->getFloorX()} {$this->location->getFloorY()} {$this->location->getFloorZ()}";
				}
			};
		}

		$tickDiff = $this->finalTick - $server->getTick();
		if($tickDiff >= 20 && Main::getPlayerSettings($fromTarget->getName())['Teleport Countdown']){
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_time(
				CustomKnownTranslationFactory::teleport_state_seconds()->prefix(ceil($tickDiff / 20) . ' ')
			));
			return;
		}

		if(!$fromTarget->teleport($toTarget->getLocation())){ // likely tp event cancelled by other plugin
			if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'] && $toTarget instanceof Player)
				$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_failed($toTarget->getName()));

			if($this->attempt === 2 || $this->request->toTarget === Main::RANDOM_KEYNAME){ // 3 attempts to teleport
				$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_failedfinal());
				throw new CancelTaskException();
			}
			$this->attempt++;

			$retryInterval = $config->get('Retry Interval', -1);
			if($retryInterval < 1){
				$this->attempt = 2; // prevent reuse of this task object
				throw new CancelTaskException();
			}

			$this->finalTick += $retryInterval * 20; // retry teleport in configured seconds
			return;
		}

		$requester = $server->getPlayerExact($this->request->requester);
		if($requester !== null){
			Command::broadcastCommandMessage( // inform admins of player teleport
				$requester,
				KnownTranslationFactory::commands_tp_success($fromTarget->getName(), $toTarget->getName())
			);
		}

		if(Main::getPlayerSettings($fromTarget->getName())['Alert Teleporting'])
			$fromTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_successfrom($toTarget->getName()));

		if(Main::getPlayerSettings($toTarget->getName())['Alert Receiver'] && $toTarget instanceof Player)
			$toTarget->sendMessage(CustomKnownTranslationFactory::teleport_state_successto($fromTarget->getName()));

		$this->request->cancel(); // request can now be removed from the queue

		throw new CancelTaskException();
	}
}
