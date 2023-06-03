<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Negated boolean expression is always false\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Receiver\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Teleporting\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Countdown\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Delay\' on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Result of \\|\\| is always false\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:\\$playerSettings \\(array\\<string, array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\>\\) does not accept non\\-empty\\-array\\<string, array\\<int\\|string, mixed\\>\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Method jasonw4331\\\\PoliteTeleports\\\\commands\\\\TpAcceptCommand\\:\\:execute\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpAcceptCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Delay\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/commands/TpAcceptCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method jasonw4331\\\\PoliteTeleports\\\\commands\\\\TpAskCommand\\:\\:execute\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpAskCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Casting to bool something that\'s already bool\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Casting to int something that\'s already int\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Dynamic call to static method jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:getPlayerSettings\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Dynamic call to static method jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:updatePlayerSettings\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method jasonw4331\\\\PoliteTeleports\\\\commands\\\\TpConfig\\:\\:execute\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Receiver\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Teleporting\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Language\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Countdown\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Delay\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$settings of method jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:updatePlayerSettings\\(\\) expects array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}, array\\{Alert Receiver\\: bool\\}\\|array\\{Alert Teleporting\\: bool\\}\\|array\\{Language\\: string\\}\\|array\\{Teleport Countdown\\: bool\\}\\|array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Language\\?\\: string\\}\\|array\\{Teleport Delay\\: int\\} given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method jasonw4331\\\\PoliteTeleports\\\\commands\\\\TpDenyCommand\\:\\:execute\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpDenyCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method jasonw4331\\\\PoliteTeleports\\\\commands\\\\TpaHereCommand\\:\\:execute\\(\\) should return mixed but return statement is missing\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/commands/TpaHereCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getConfig\\(\\) on pocketmine\\\\plugin\\\\Plugin\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Receiver\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Alert Teleporting\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'Teleport Countdown\' does not exist on array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool\\}\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$source of static method pocketmine\\\\command\\\\Command\\:\\:broadcastCommandMessage\\(\\) expects pocketmine\\\\command\\\\CommandSender, pocketmine\\\\player\\\\Player\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
