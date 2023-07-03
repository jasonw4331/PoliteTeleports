<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports;

use InvalidArgumentException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;
use function array_merge;
use function is_bool;
use function is_int;
use function max;
use function mb_strtolower;
use function min;
use function mkdir;
use function pathinfo;
use function scandir;
use function yaml_parse_file;

class Main extends PluginBase implements Listener{

	public CONST RANDOM_KEYNAME = "random";

	/** @var array<string, Language> $languages */
	private static array $languages = [];
	/** @var TeleportRequest[][] $activeRequests */
	private array $activeRequests = [];
	/** @phpstan-var array<string, array{
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool,
	 * "Random Location Radius": int,
	 * "Random Location Safety": bool
	 * }> $playerSettings
	 */
	private static array $playerSettings = [];

	public function onEnable() : void{
		// register commands
		$this->getServer()->getCommandMap()->registerAll($this->getName(), [
			new commands\TpAskCommand($this),
			new commands\TpAcceptCommand($this),
			new commands\TpConfig($this),
			new commands\TpDenyCommand($this),
			new commands\TpaHereCommand($this),
			new commands\TpRandomCommand($this),
		]);

		// register events
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveResource('/lang/config.yml');
		/** @var string[][] $contents */
		$contents = yaml_parse_file(Path::join($this->getDataFolder(), "lang", 'config.yml'));
		$languageAliases = [];
		foreach($contents as $language => $aliases){
			$mini = mb_strtolower($aliases['mini']);
			$this->saveResource('/lang/data/' . $mini . '.ini');
			$languageAliases[$mini] = $language;
		}

		$dir = scandir(Path::join($this->getDataFolder(), "lang", "data"));
		if($dir !== false){
			foreach($dir as $file){
				/** @phpstan-var array{dirname: string, basename: string, extension?: string, filename: string} $fileData */
				$fileData = pathinfo($file);
				if(!isset($fileData["extension"]) || $fileData["extension"] !== "ini"){
					continue;
				}
				$languageName = mb_strtolower($fileData["filename"]);
				$language = new Language(
					$languageName,
					Path::join($this->getDataFolder(), "lang", "data")
				);
				self::$languages[$languageName] = $language;
				foreach($languageAliases as $languageAlias => $alias) {
					if(mb_strtolower($alias) === $languageName){
						self::$languages[mb_strtolower($languageAlias)] = $language;
						unset($languageAliases[$languageAlias]);
					}
				}
			}
		}
		$this->updateLanguage($this->getConfig()->get("Language", $this->getServer()->getLanguage()->getLang()));

		@mkdir(Path::join($this->getDataFolder(), "players"));

		// garbage collection cleans cancelled requests every 5 minutes
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(){
			foreach($this->activeRequests as $requester => $requests){
				foreach($requests as $key => $request){
					if($request->isCancelled()){
						unset($this->activeRequests[$requester][$key]);
					}
				}
			}
		}), 20 * 60 * 5);
	}

	public function onPlayerLogin(PlayerLoginEvent $event) : void{
		$player = $event->getPlayer();
		// this always must be kept in sync with the config.yml
		$defaults = [
			"Teleport Delay" => 5,
			"Teleport Countdown" => true,
			"Alert Teleporting" => true,
			"Alert Receiver" => true,
			"Random Location Radius" => 10000,
			"Random Location Safety" => true
		];
		$playerConfig = new Config(
			Path::join($this->getDataFolder(), "players", $player->getName() . ".json"),
			Config::JSON,
			(array) $this->getConfig()->get("Defaults", $defaults)
		);
		foreach(array_merge($defaults, $playerConfig->getAll()) as $key => $value) {
			self::$playerSettings[$player->getName()][$key] = match($key) {
				"Teleport Delay" => max($value, $defaults["Teleport Delay"]),
				"Random Location Radius" => min($value, $defaults["Random Location Radius"]),
				default => $value
			};
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		if(!$player->spawned){ // prevent crash in case of login timeout
			return;
		}
		unset(self::$playerSettings[$player->getName()]);

		// cancel all teleport requests
		if(isset($this->activeRequests[$player->getName()])){
			foreach($this->activeRequests[$player->getName()] as $request){
				$request->cancel();
			}
			unset($this->activeRequests[$player->getName()]);
		}
	}

	/**
	 * @return array<string, Language>
	 */
	public static function getLanguages() : array{
		return self::$languages;
	}

	/**
	 * @phpstan-param string $language accepts any known translation language name or alias
	 */
	public function updateLanguage(string $language) : void{
		if(!isset(self::$languages[$language]))
			return;

		// add translations to existing server language instance
		$languageA = $this->getServer()->getLanguage();
		$refClass = new ReflectionClass($languageA::class);
		$refPropA = $refClass->getProperty('lang');
		/** @var string[] $langA */
		$langA = $refPropA->getValue($languageA);
		/** @var string[] $langB */
		$langB = $refClass->getProperty('lang')->getValue(self::$languages[$language]);
		$refPropA->setValue($languageA, array_merge($langA, $langB));
	}

	public function addRequest(string $fromTarget, string $toTarget, string $requester) : void{
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
	public function getActiveRequests() : array{
		return $this->activeRequests;
	}

	/**
	 * @phpstan-return array{
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool,
	 * "Random Location Radius": int,
	 * "Random Location Safety": bool
	 * }
	 */
	public static function getPlayerSettings(string $playerName) : array{
		return self::$playerSettings[$playerName];
	}

	/**
	 * @phpstan-param array{
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool,
	 * "Random Location Radius": int,
	 * "Random Location Safety": bool
	 * } $settings
	 */
	public static function updatePlayerSettings(string $playerName, array $settings) : void{
		// validate settings
		if(!isset(self::$playerSettings[$playerName]))
			throw new InvalidArgumentException("Player $playerName does not exist");
		if(!isset($settings["Teleport Delay"]) || !is_int($settings["Teleport Delay"]))
			throw new InvalidArgumentException("Teleport Delay must be an integer");
		if(!isset($settings["Teleport Countdown"]) || !is_bool($settings["Teleport Countdown"]))
			throw new InvalidArgumentException("Teleport Countdown must be a boolean");
		if(!isset($settings["Alert Teleporting"]) || !is_bool($settings["Alert Teleporting"]))
			throw new InvalidArgumentException("Alert Teleporting must be a boolean");
		if(!isset($settings["Alert Receiver"]) || !is_bool($settings["Alert Receiver"]))
			throw new InvalidArgumentException("Alert Receiver must be a boolean");
		if(!isset($settings["Random Location Radius"]) || !is_int($settings["Random Location Radius"]))
			throw new InvalidArgumentException("Random Location Radius must be an integer");
		if(!isset($settings["Random Location Safety"]) || !is_bool($settings["Random Location Safety"]))
			throw new InvalidArgumentException("Random Location Safety must be a boolean");

		self::$playerSettings[$playerName] = $settings;
	}
}
