<?php
#センサーがホストの $url のアドレスに自機のデータを送信する。
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";

$sensorId = 2;
$url = "http://localhost/API/insertDiscvLog.php";
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));
$lastTimeArr = $Sensor2->getLastLogTime($sensorId, EXCLUSION);
var_dump($lastTimeArr);
$newDiscvLog = $Sensor2->getDiscvLog($sensorId,array_keys($lastTimeArr)[0],EXCLUSION);

$postData = json_encode($newDiscvLog);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$discvJSON =  curl_exec($ch);
curl_close($ch);