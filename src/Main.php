<?php

declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;
use function is_bool;
use function is_int;

class Main extends PluginBase implements Listener{

	/** @var TeleportRequest[][] $activeRequests */
	private array $activeRequests = [];
	/** @var array<string, array> $playerSettings */
	private static array $playerSettings = [];

	public function onEnable() : void {
		// register commands
		$this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new commands\TpAskCommand($this),
			new commands\TpAcceptCommand($this),
			new commands\TpConfig($this),
			new commands\TpDenyCommand($this),
			new commands\TpaHereCommand($this),
		]);

		// garbage collection cleans cancelled requests every 5 minutes
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(\Closure::fromCallable(
			function() {
				foreach($this->activeRequests as $requester => $requests) {
					foreach($requests as $key => $request) {
						if($request->isCancelled()) {
							unset($this->activeRequests[$requester][$key]);
						}
					}
				}
			}
		)), 20 * 60 * 5);
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$playerConfig = new Config(
			Path::join($this->getDataFolder(), "players", $player->getName() . ".json"),
			Config::JSON,
			(array) $this->getConfig()->get("Defaults", [
				"Teleport Delay" => 5,
				"Teleport Countdown" => true,
				"Alert Teleporting" => true,
				"Alert Receiver" => true,
			])
		);
		self::$playerSettings[$player->getName()] = $playerConfig->getAll();
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		unset(self::$playerSettings[$player->getName()]);

		// cancel all teleport requests
		foreach($this->activeRequests[$player->getName()] as $request) {
			$request->cancel();
		}
	}

	public function addRequest(string $fromTarget, string $toTarget, string $requester) : void {
		$request = new TeleportRequest($fromTarget, $toTarget, $requester);

		if(!isset($this->activeRequests[$fromTarget]))
			$this->activeRequests[$fromTarget] = [];
		$this->activeRequests[$fromTarget][] = &$request;

		if(!isset($this->activeRequests[$toTarget]))
			$this->activeRequests[$toTarget] = [];
		$this->activeRequests[$toTarget][] = &$request;
	}

	/**
	 * @return TeleportRequest[][]
	 */
	public function getActiveRequests() : array {
		return $this->activeRequests;
	}

	/**
	 * @return array<int|bool|string>|null
	 */
	public static function getPlayerSettings(string $playerName) : ?array {
		return self::$playerSettings[$playerName];
	}

	/**
	 * @param array<int|bool|string>  $settings
	 */
	public static function updatePlayerSettings(string $playerName, array $settings) : void {
		// validate settings
		if(!isset(self::$playerSettings[$playerName]))
			throw new \InvalidArgumentException("Player $playerName does not exist");

		if(!isset($settings["Teleport Delay"]) || !is_int($settings["Teleport Delay"]))
			throw new \InvalidArgumentException("Teleport Delay must be an integer");
		if(!isset($settings["Teleport Countdown"]) || !is_bool($settings["Teleport Countdown"]))
			throw new \InvalidArgumentException("Teleport Countdown must be a boolean");
		if(!isset($settings["Alert Teleporting"]) || !is_bool($settings["Alert Teleporting"]))
			throw new \InvalidArgumentException("Alert Teleporting must be a boolean");
		if(!isset($settings["Alert Receiver"]) || !is_bool($settings["Alert Receiver"]))
			throw new \InvalidArgumentException("Alert Receiver must be a boolean");

		self::$playerSettings[$playerName] = $settings;
	}
}
