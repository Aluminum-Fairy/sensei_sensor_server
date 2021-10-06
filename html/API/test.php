<?php

require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../config/Config.php";
$Sensor = new Sensor($loginInfo);

$result = $Sensor->getAllowedDiscvList();
var_dump($result);