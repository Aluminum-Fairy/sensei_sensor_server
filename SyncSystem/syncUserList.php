<?php

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ ."/../lib/UserInfo.php";
require_once __DIR__ ."/../lib/Tools.php";

$UserInfo = new UserInfo($loginInfo);

$lastUpdateList = $UserInfo->getLastUserUpdatTime();

$resStr = postCurl("http://".URL."/SyncAPI/", json_encode($lastUpdateList));
$resArr = json_decode($resStr, true);

#ユーザーの更新があった場合は更新,または新規挿入
foreach ($resArr["change"] as $userInfo) {
    $UserInfo->setSensorsUser($userInfo["userId"], $userInfo["userName"], $userInfo["description"], $userInfo["updateTime"]);
}
