<?php

require_once(__DIR__ . "/../../lib/Sensor.php");
require_once(__DIR__."/../../lib/JwtAuth.php");

$JwtAuth = new JwtAuth($loginInfo);
$Sensor = new Sensor($loginInfo);
header("Content-Type:application/json;charset=utf-8");
$result ["notFoundUserList"] = $Sensor->getNotFoundDiscvList(30);
$result ["discoveryUserList"] = $Sensor->getAllDiscvList(30);

echo json_encode($result);
