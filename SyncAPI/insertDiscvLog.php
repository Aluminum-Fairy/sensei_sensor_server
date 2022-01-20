<?php

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);

$json = file_get_contents("php://input");

$sensorInfo = json_decode($json, true);

foreach ($sensorInfo as $sensorInfoArr) {
    $Sensor->inputDiscvLog($sensorInfoArr['time'], $sensorInfoArr['sensorId'], $sensorInfoArr['userId']);
}
