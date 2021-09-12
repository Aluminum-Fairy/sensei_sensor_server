<?php
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);

$json = file_get_contents("php://input");

$sensorInfo = json_decode($json,true);

$result =[];
foreach ($sensorInfo as $sensorArr){
    $result+=$Sensor->getDiscvLogSetTime($sensorArr["sensorId"], $sensorArr["time"]);
}
print(json_encode($result));