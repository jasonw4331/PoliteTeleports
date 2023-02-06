<?php

declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\lang\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;
use function in_array;
use function is_bool;
use function is_int;
use function is_string;
use function mb_strtolower;
use function pathinfo;
use function scandir;
use function yaml_parse_file;

class Main extends PluginBase implements Listener{
	/** @var array<string, Language> $languages */
	private static array $languages = [];
	/** @var TeleportRequest[][] $activeRequests */
	private array $activeRequests = [];
	/** @phpstan-var array{
	 * Language: string,
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool
	 * } $playerSettings
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
		]);

		// register events
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveResource('/lang/config.yml');
		/** @var string[][] $contents */
		$contents = yaml_parse_file(Path::join($this->getDataFolder(), "lang", 'config.yml'));
		$languageAliases = [];
		foreach($contents as $language => $aliases){
			$this->saveResource('/lang/data/' . $language . '.ini');
			$languageAliases[mb_strtolower($aliases['mini'])] = $language;
		}

		$dir = scandir(Path::join($this->getDataFolder(), "lang", "data"));
		if ($dir !== false) {
			foreach ($dir as $file) {
				/** @phpstan-var array{dirname: string, basename: string, extension?: string, filename: string} $fileData */
				$fileData = pathinfo($file);
				if (!isset($fileData["extension"]) || $fileData["extension"] !== "ini") {
					continue;
				}
				$languageName = mb_strtolower($fileData["filename"]);
				$language = new Language(
					$languageName,
					Path::join($this->getDataFolder(), "lang", "data"),
					Language::FALLBACK_LANGUAGE
				);
				self::$languages[$languageName] = $language;
				foreach ($languageAliases as $languageAlias => $alias) {
					if (mb_strtolower($alias) === $languageName) {
						self::$languages[mb_strtolower($languageAlias)] = $language;
						unset($languageAliases[$languageAlias]);
					}
				}
			}
		}

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
				"Language" => "eng",
				"Teleport Delay" => 5,
				"Teleport Countdown" => true,
				"Alert Teleporting" => true,
				"Alert Receiver" => true,
			])
		);
		self::$playerSettings[$player->getName()] = $playerConfig->getAll();

		// add translations to existing player language instance
		$language = $player->getLanguage();
		$refClass = new \ReflectionClass($language);
		$refProp = $refClass->getProperty('lang');
		$refProp->setAccessible(true);
		$lang = $refProp->getValue($language);
		$lang = array_merge($lang, self::$languages[$playerConfig->get('Language', 'eng')]->getAll());
		$refProp->setValue($language, $lang);
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();
		unset(self::$playerSettings[$player->getName()]);

		// cancel all teleport requests
		foreach($this->activeRequests[$player->getName()] as $request) {
			$request->cancel();
		}
	}

	/**
	 * @return array<string, Language>
	 */
	public static function getLanguages() : array{
		return self::$languages;
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
	 * @phpstan-return array{
	 * Language: string,
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool
	 * }|null
	 */
	public static function getPlayerSettings(string $playerName) : ?array {
		return self::$playerSettings[$playerName];
	}

	/**
	 * @phpstan-param array{
	 * Language: string,
	 * "Teleport Delay": int,
	 * "Teleport Countdown": bool,
	 * "Alert Teleporting": bool,
	 * "Alert Receiver": bool
	 * } $settings
	 */
	public static function updatePlayerSettings(string $playerName, array $settings) : void {
		// validate settings
		if(!isset(self::$playerSettings[$playerName]))
			throw new \InvalidArgumentException("Player $playerName does not exist");
		if(!isset($settings["Language"]) || !is_string($settings["Language"]) || !in_array($settings["Language"], array_keys(self::$languages), true))
			throw new \InvalidArgumentException("Language must be a string");
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
