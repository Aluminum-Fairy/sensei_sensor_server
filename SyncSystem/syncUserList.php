<?php

##センサー側のユーザリスト(パスワードを除く)同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/UserInfo.php";
require_once __DIR__ . "/../lib/Tools.php";
require_once __DIR__ . "/../lib/LogClass.php";

$UserInfo = new UserInfo($loginInfo);
$Log = new LogClass(__FILE__);
#センサーに格納された各ユーザーの設定が変更されたとされる時刻リストを取得
$lastUpdateList = $UserInfo->getLastUserUpdateTime();
$Log->Systemlog("各ユーザー設定時刻情報送信", $lastUpdateList);
$resStr = postCurl("http://" . URL . "/SyncAPI/getUserUpdate.php", json_encode($lastUpdateList));
$Log->Systemlog("各ユーザー設定受信", $lastUpdateList);
$resArr = json_decode($resStr, true);
# センサーリストのうち、変更と新規追加があった場合はこちらで処理される。
foreach ($resArr["change"] as $userInfo) {
    $UserInfo->setUser($userInfo);
}
