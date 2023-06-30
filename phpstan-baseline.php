<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Receiver\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Teleporting\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Random Location…\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Countdown\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Delay\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Result of \\|\\| is always false\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:\\$playerSettings \\(array\\<string, array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\}\\>\\) does not accept non\\-empty\\-array\\<string, non\\-empty\\-array\\<int\\|string, mixed\\>\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\plugin\\\\Plugin\\:\\:getConfig\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Casting to bool something that\'s already bool\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Casting to int something that\'s already int\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Language\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\}\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method class@anonymous/task/HandleTeleportTask\\.php\\:73\\|pocketmine\\\\player\\\\Player\\:\\:sendMessage\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getConfig\\(\\) on pocketmine\\\\plugin\\\\Plugin\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$source of static method pocketmine\\\\command\\\\Command\\:\\:broadcastCommandMessage\\(\\) expects pocketmine\\\\command\\\\CommandSender, pocketmine\\\\player\\\\Player\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
