<?php

use PhpMyAdmin\Utils\HttpRequest;

require_once(__DIR__ . "/../../../lib/UserGroup.php");
require_once(__DIR__."/../../../lib/Sensor.php");
$UserGroup = new UserGroup($loginInfo);
$Sensor = new Sensor($loginInfo);
if(($result = $UserGroup->getGroupName($groupId)) !==false){
    $result += array("users"=>$Sensor->getAllowedGroupUsersDiscvList($groupId));
    echo json_encode($result);
}else{
    http_response_code(400);
}
