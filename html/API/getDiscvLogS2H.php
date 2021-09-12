<?php
ini_set('display_errors', "On");
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);
#$Sensor2 = new Sensor(array('mysql:host=localhost;dbname=sensei_sensor2;charset=utf8', $db_user, $db_pass));

foreach (["sensorId","time"] as $v) {
    if (false === $$v = filter_input(INPUT_POST, $v)) {
        http_response_code(400);
        exit();
    }
}

if($result = $Sensor->getDiscvLog($sensorId,$time)){
    http_response_code(200);
    print(json_encode($result));
} else {
    http_response_code(400);
}