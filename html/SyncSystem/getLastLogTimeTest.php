<?php
#センサー側の同期システム
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/Tools.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

$sensorId = 2;

var_dump($Sensor2->getLastLogTime($sensorId, EXCLUSION));