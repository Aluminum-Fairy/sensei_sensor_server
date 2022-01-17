<?php

require_once(__DIR__ . "/../../../lib/UserGroup.php");
require_once(__DIR__ . "/../../../lib/UserInfo.php");
require_once(__DIR__ . "/../../../lib/JwtAuth.php");
$UserGroup = new UserGroup($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$JwtAuth = new JwtAuth($loginInfo);

$userId = $JwtAuth->auth();
$userId = 1;
if ($userId !== false) {
    $result = $UserInfo->getViewDays($userId);
    $result += $UserInfo->getViewTime($userId);
    $result +=$UserInfo->getViewSensorConfig($userId);
    echo json_encode($result);
}
