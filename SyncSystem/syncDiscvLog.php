<?php

#センサー側の同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/Sensor.php";
require_once __DIR__ . "/../lib/Tools.php";
require_once __DIR__ . "/../config/SensorConfig.php";
require_once __DIR__ . "/../lib/LogClass.php";

$Sensor = new Sensor($loginInfo);
$Log = new LogClass(__FILE__);

#DB上の各センサーの最新時刻を送信
$payload = array("discvLogTime" => $Sensor->getLastLogTime(ThisSensorId, EXCLUSION));
$payload += array("thisSensorId" => ThisSensorId);
$postData = json_encode($payload);
$Log->Systemlog("各センサー時刻情報送信",$payload);

#サーバーへ送信、各センサーの差分データを受信,
$resStr = postCurl("http://" . URL . "/SyncAPI/getLastLogTime.php", $postData);
$Log->Systemlog("各センサー時刻情報受信",$resStr);

#応答元のセンサーの最新時刻(サーバー内の)を受信、入力
$resArr = json_decode($resStr, true);
foreach ($resArr["newDiscvLog"] as $discvLog) {
    $Sensor->inputDiscvLog($discvLog['time'], $discvLog['sensorId'], $discvLog['userId']);
}

#応答元のセンサーの差分データをサーバへ送信
$newDiscvLog = $Sensor->getDiscvLog(ThisSensorId, $resArr["lastLogTime"][0]["time"], MATCH);
$Log->Systemlog("差分データ送信",$newDiscvLog);
postCurl("http://" . URL . "/SyncAPI/insertDiscvLog.php", json_encode($newDiscvLog));
