<?php

use Firebase\JWT\JWT;

require_once(__DIR__ . "/../../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../../lib/UserInfo.php");
header('Content-Type: application/json');

$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$userId = $JWT->auth();

if ($userId !== false) {
    $result = $UserInfo->getViewSensorConfig($userId);
    if($result !== false){
        echo json_encode($result);
        http_response_code(200);
    }else{
        http_response_code(500);
    }
}
