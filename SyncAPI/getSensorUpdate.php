<?php

require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../config/Config.php";


$Sensor = new Sensor($loginInfo);

#POSTで送信されたデータ
$json = file_get_contents("php://input");
$sensorInfoArr = json_decode($json, true);

#結果を入れる配列の準備
$result = array("change" => array());
$result += array("delete" => array());

#新たに追加されたセンサーをセンサーIDから検索、$result["change"]に格納
$sensorListFromDB = $Sensor->getSensorIdList();
$sensorListFromReq = array();
foreach ($sensorInfoArr as $sensorInfo) {
    $sensorListFromReq[] = $sensorInfo["sensorId"];
}
$newSenosrIdArr = array_diff($sensorListFromDB, $sensorListFromReq);             #センサーリストの比較、無いIDだけピックアップ
foreach ($newSenosrIdArr as $newSenosrId) {
    $result["change"][] = $Sensor->getSensorInfo($newSenosrId);
}

#センサーの更新時間とIDから変更点を検索
#削除された場合checkSensorUpdate()は0を返す
foreach ($sensorInfoArr as $sensorInfo) {
    $res = $Sensor->checkSensorUpdate($sensorInfo);
    if (false === $res) {
    } elseif ($res === 0) {
        $result["delete"][] = $sensorInfo["sensorId"];
    } else {
        $result["change"][] = $res;
    }
}

print(json_encode($result));
