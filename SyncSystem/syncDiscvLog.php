<?php

#センサー側の同期システム

require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/Tools.php";
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../config/SensorConfig.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));
#DB上の各センサーの最新時刻を送信
$payload = array("discvLogTime" => $Sensor2->getLastLogTime(ThisSensorId, EXCLUSION));
var_dump($payload);
$payload += array("thisSensorId" => ThisSensorId);
$postData = json_encode($payload);

#サーバーへ送信、各センサーの差分データを受信,
$resStr = postCurl("http://" . URL . "/SyncAPI/getLastLogTime.php", $postData);

#応答元のセンサーの最新時刻(サーバー内の)を受信、入力
$resArr = json_decode($resStr, true);

foreach ($resArr["newDiscvLog"] as $discvLog) {
    var_dump($discvLog);
    $Sensor2->inputDiscvLog($discvLog['time'], $discvLog['sensorId'], $discvLog['userId']);
}

#応答元のセンサーの差分データをサーバへ送信
$newDiscvLog = $Sensor2->getDiscvLog(ThisSensorId, $resArr["lastLogTime"][0]["time"], match);
postCurl("http://" . URL . "/SyncAPI/insertDiscvLog.php", json_encode($newDiscvLog));
