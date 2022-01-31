<?php

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../lib/UserInfo.php");
$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$userId = $JWT->auth();


if ($userId !== false) {
    $json = file_get_contents("php://input");
    $pubTimeInfo = json_decode($json, true);
    $UserInfo->setAllWeekCfg($userId, $pubTimeInfo["publicationTime"]["start"], $pubTimeInfo["publicationTime"]["end"]);
}