<?php

##センサー側のユーザリスト(パスワードを除く)同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/UserInfo.php";
require_once __DIR__ . "/../lib/Tools.php";

$UserInfo = new UserInfo($loginInfo);

#センサーに格納された各ユーザーの設定が変更されたとされる時刻リストを取得
$lastUpdateList = $UserInfo->getLastUserUpdateTime();

$resStr = postCurl("http://" . URL . "/SyncAPI/getUserUpdate.php", json_encode($lastUpdateList));

$resArr = json_decode($resStr, true);
# センサーリストのうち、変更と新規追加があった場合はこちらで処理される。
foreach ($resArr["change"] as $sensorInfo) {
    $UserInfo->setUser($sensorInfo);
}
