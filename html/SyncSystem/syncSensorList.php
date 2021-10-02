<?php
#センサー側のセンサーリスト同期システム

require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/Tools.php";
require_once __DIR__ . "/../../config/Config.php";
require_once __DIR__ . "/../../config/SensorConfig.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

$lastUpdateList=$Sensor2->getLastSensorUpdateTime();

$resStr =  postCurl("http://".URL."/API/getSensorUpdate.php",json_encode($lastUpdateList));

$resArr = json_decode($resStr,true);

foreach ($resArr["change"] as $sensorInfo){
    $Sensor2->setSensor($sensorInfo);
}

foreach ($resArr["delete"] as $sensorId){
    $Sensor2->deleteSenor($sensorId);
}