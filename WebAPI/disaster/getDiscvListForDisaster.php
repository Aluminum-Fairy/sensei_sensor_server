<?php

require_once(__DIR__ . "/../../lib/Sensor.php");
require_once(__DIR__."/../../lib/JwtAuth.php");

$JwtAuth = new JwtAuth($loginInfo);
$Sensor = new Sensor($loginInfo);

$result []= array("NotFoundUserList"=>$Sensor->getNotFoundDiscvList(30));
$result []= array("discoveryUserList"=>$Sensor->getAllDiscvList(30));

echo json_encode($result);
