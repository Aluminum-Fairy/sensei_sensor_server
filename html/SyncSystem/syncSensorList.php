<?php
#センサー側のセンサーリスト同期システム
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/Tools.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

$lastUpdateList=$Sensor2->getLastSensorUpdateTime();

$resStr =  postCurl("http://localhost/API/getSensorUpdate.php",json_encode($lastUpdateList));

$resArr = json_decode($resStr,true);
foreach ($resArr["change"] as $sensorInfo){
    $Sensor2->addSensor($sensorInfo);
}

foreach ($resArr["delete"] as $sensorId){
    $Sensor2->deleteSenor($sensorId);
}