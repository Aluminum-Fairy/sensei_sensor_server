<?php

require_once __DIR__ . "/../../lib/Sensor.php";
require_once __DIR__ . "/../../lib/UserGroup.php";
require_once __DIR__ . "/../config/Config.php";
$Sensor = new Sensor($loginInfo);
$UserGroup = new UserGroup($loginInfo);
$result = $Sensor->getAllowedDiscvList();
var_dump($result);

var_dump($UserGroup->getGroupList());
