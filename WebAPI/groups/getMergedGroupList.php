<?php

require_once(__DIR__ . "/../../lib/UserGroup.php");
require_once(__DIR__ . "/../../lib/Sensor.php");

$UserGroup = new UserGroup($loginInfo);

$jsonText = file_get_contents("php://input");
$groupList = json_decode($jsonText,true);

$Sensor = new Sensor($loginInfo);
$result = array();

foreach ($groupList["groupId"] as $groupId){
    if (($tempRes = $UserGroup->getGroupName($groupId)) !== false) {
        $tempRes +=array("users" => $Sensor->getAllowedGroupUsersDiscvList($groupId));
        $result[] = $tempRes;
    } else {
        http_response_code(400);
        exit();
    }
}

echo json_encode($result);