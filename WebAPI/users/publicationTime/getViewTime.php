<?php

use Firebase\JWT\JWT;

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../lib/UserInfo.php");


$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$userId = $JWT->auth();

if ($userId !== false) {
    echo json_encode($UserInfo->getViewTime($userId));
}
