<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Cannot cast mixed to string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:\\$playerSettings \\(array\\<string, array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\}\\>\\) does not accept non\\-empty\\-array\\<string, array\\<string, bool\\|int\\>\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Static property jasonw4331\\\\PoliteTeleports\\\\Main\\:\\:\\$playerSettings \\(array\\<string, array\\{Teleport Delay\\: int, Teleport Countdown\\: bool, Alert Teleporting\\: bool, Alert Receiver\\: bool, Random Location Radius\\: int, Random Location Safety\\: bool\\}\\>\\) does not accept non\\-empty\\-array\\<string, non\\-empty\\-array\\<int\\|string, mixed\\>\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Main.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method pocketmine\\\\plugin\\\\Plugin\\:\\:getConfig\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/task/HandleTeleportTask.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
