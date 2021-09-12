<?php
#センサーがホストの $url のアドレスに自機のデータを送信する。
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

$sensorId = 2;
$url = "http://localhost/API/getDiscvLog.php";
$lastTimeArr = $Sensor2->getLastLogTime($sensorId,Sensor);
$postData = json_encode($lastTimeArr);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$discvJSON =  curl_exec($ch);
curl_close($ch);

$result = json_decode($discvJSON, true);
foreach ($result as $resultArr) {
    $Sensor2->inputDiscvLog($resultArr['time'], $resultArr['sensorId'], $resultArr['userId']);
}
