<?php

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../lib/UserInfo.php");
require_once(__DIR__ . "/../../../lib/Weeks.php");
$JWT = new JwtAuth($loginInfo);
$UserInfo = new UserInfo($loginInfo);
$Weeks = new Weeks();
$userId = $JWT->auth();
//$userId = 1;


if ($userId !== false) {
    $json = file_get_contents("php://input");
    $pubDayInfo = json_decode($json, true);
    $UserInfo->beginTransaction();
    foreach ($pubDayInfo as $week => $pubCfg) {
        if (!$UserInfo->setPubViewTimeCfg($userId, $Weeks->getWeekNum($week), $pubCfg)) {
            $UserInfo->rollBack();
            $UserInfo->Systemlog(__FILE__, ROLLBACK_Message);
            http_response_code(500);
            exit();
        }
    }
    $UserInfo->commit();
}
