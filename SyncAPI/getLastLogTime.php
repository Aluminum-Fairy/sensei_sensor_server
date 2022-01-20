<?php

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../../lib/Sensor.php";



$Sensor = new Sensor($loginInfo);

$json = file_get_contents("php://input");

$sensorInfo = json_decode($json, true);
$result = [];
$result += array("newDiscvLog" => array());
foreach ($sensorInfo["discvLogTime"] as $sensorArr) {
    $result["newDiscvLog"] += ($Sensor->getDiscvLog($sensorArr["sensorId"], $sensorArr["time"], match));
}
$result += array("lastLogTime" => array());
$result["lastLogTime"] = $Sensor->getLastLogTime($sensorInfo["thisSensorId"], match);

print(json_encode($result));
