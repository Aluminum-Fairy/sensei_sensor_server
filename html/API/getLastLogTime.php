<?php
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);

foreach (["sensorId"] as $v) {
    if (false === $$v = filter_input(INPUT_POST, $v)) {
        http_response_code(400);
        exit();
    }
}

print(json_encode($Sensor->getLastLogTimeSensor($sensorId)));
