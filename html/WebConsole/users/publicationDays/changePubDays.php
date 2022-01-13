<?php

require_once(__DIR__ . "/../../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../../lib/UserInfo.php");
require_once(__DIR__ . "/../../../../lib/Weeks.php");
$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$userId = $JWT->auth();
//$userId = 1;


if($userId !== false){
    $json = file_get_contents("php://input");
    $pubDayInfo = json_decode($json, true);
    $UserInfo->beginTransaction();
    foreach($pubDayInfo["publicationDays"] as $week => $pubCfg){
        if(!$UserInfo->setPubViewCfg($userId, getWeekNum($week), $pubCfg)){
            $UserInfo->rollBack();
            http_response_code(500);
            exit();
        }
    }
    $UserInfo->commit();

}