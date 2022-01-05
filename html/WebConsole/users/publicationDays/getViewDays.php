<?php

use Firebase\JWT\JWT;

require_once(__DIR__ . "/../../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../../lib/UserInfo.php");
require_once(__DIR__."/../../../../lib/Weeks.php");
$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
//$userId = $JWT->auth();
$userId = 1;

if ($userId !== false) {
    $config = $UserInfo->getViewConfig($userId);
    $result = array("publicationDays"=>array());
    foreach($config as $weekNum => $weekConfig){
        $result["publicationDays"] += array(getWeek($weekNum-1)=>$weekConfig["publicView"] == 1);
    }
    echo json_encode($result);
}
