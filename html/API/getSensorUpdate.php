<?php
require_once __DIR__ . "/../../lib/Sensor.php";
ini_set('display_errors', "On");
$Sensor = new Sensor($loginInfo);

$json = file_get_contents("php://input");

$sensorInfoArr = json_decode($json, true);

$result = array("change"=>array());
$result += array("delete"=>array());


$sensorListFromDB = $Sensor->getSensorIdList();
$sensorListFromReq = array();
foreach($sensorInfoArr as $sensorInfo){
    $sensorListFromReq[] = $sensorInfo["sensorId"];
}
$newSenosrIdArr = array_diff_assoc($sensorListFromDB,$sensorListFromReq);
foreach($newSenosrIdArr as $newSenosrId){
    $result["change"] += $Sensor->getSensorInfo($newSenosrId);
}
foreach ($sensorInfoArr as $sensorInfo) {
    $res = $Sensor->checkSensorUpdate($sensorInfo);
    if (False === $res) {
    }elseif($res ===0){
        $result["delete"] +=array($sensorInfo["sensorId"]);
    }else{
        $result["change"] += array($res);
    }
}


print(json_encode($result));