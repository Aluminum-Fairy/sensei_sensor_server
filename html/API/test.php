<?php
require_once __DIR__ . "/../../lib/Sensor.php";

$Sensor = new Sensor($loginInfo);

print(json_encode($Sensor->getLastLogTimeSensor()));