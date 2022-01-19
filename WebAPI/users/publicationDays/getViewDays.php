<?php

use Firebase\JWT\JWT;

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../lib/UserInfo.php");
require_once(__DIR__ . "/../../../lib/Weeks.php");


$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$Weeks = new Weeks();
//$userId = $JWT->auth();
$userId = 1;

if ($userId !== false) {
    echo json_encode($UserInfo->getViewDays($userId));
}
