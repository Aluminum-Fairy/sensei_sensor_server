<?php

use Firebase\JWT\JWT;

require_once(__DIR__ . "/../../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../../lib/UserInfo.php");
$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$userId = $JWT->auth();

if ($userId !== false) {
    $config = $UserInfo->getViewConfig($userId);
    $result = array("publicationTime"=>array("start"=>$config[0]["startTime"]));
    $result["publicationTime"] += array("end"=>$config[0]["endTime"]);
    echo json_encode($result);
}
