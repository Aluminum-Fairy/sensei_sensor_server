<?php

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/Sensor.php";


$Sensor = new Sensor($loginInfo);

foreach (["sensorId", "placeName", "isMaster", "isWebServer"] as $v) {
    if (false === $$v = filter_input(INPUT_POST, $v)) {
        http_response_code(400);
        exit();
    }
}

if ($Sensor->setSensor($sensorId, $placeName, $isMaster, $isWebServer)) {
    http_response_code(200);
} else {
    http_response_code(400);
}
