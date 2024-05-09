<?php

require_once(__DIR__ . "/../../lib/UserGroup.php");
require_once(__DIR__ . "/../../lib/UserInfo.php");
require_once(__DIR__ . "/../../lib/JwtAuth.php");
$UserGroup = new UserGroup($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$JwtAuth = new JwtAuth($loginInfo);

$userId = $JwtAuth->auth();
if ($userId !== false) {
    $result = array("publicationDays" => $UserInfo->getViewDays($userId));
    $result += array("publicationTime" => $UserInfo->getViewTime($userId));
    $result += array( "publicationPlace" => $UserInfo->getViewSensorConfig($userId));
    $result += array("groupList" => array("groups" => $UserGroup->getUserFromGroupList($userId)));
    echo json_encode($result);
    http_response_code(200);
} else {
    http_response_code(401);
}
