<?php
#センサー側の同期システム
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/Tools.php";

$Sensor = new Sensor($loginInfo);
$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

#DB上の各センサーの最新時刻を送信

$sensorId=2;
$payload = array("discvLogTime"=>$Sensor2->getLastLogTime($sensorId, EXCLUSION));
$payload += array("thisSensorId"=>$sensorId);
$postData = json_encode($payload);

##サーバーへ送信
$resStr=postCurl("http://localhost/API/getLastLogTime.php",$postData);

#各センサーの差分データを受信,
#応答元のセンサーの最新時刻(サーバー内の)を受信

$resArr = json_decode($resStr,true);

foreach($resArr["newDiscvLog"] as $discvLog){
    $insertRes = $Sensor2->inputDiscvLog($discvLog['time'], $discvLog['sensorId'], $discvLog['userId']);
    if($insertRes){
        print("InsertSuccess\n");
    }else{
        print("InsertFailed :". $discvLog['sensorId']."\n");
    }
}

$newDiscvLog = $Sensor2->getDiscvLog($sensorId,$resArr["lastLogTime"][0]["time"],MATCH);

postCurl("http://localhost/API/insertDiscvLog.php",json_encode($newDiscvLog));