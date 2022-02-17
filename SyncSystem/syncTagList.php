<?php

##センサー側のタグリスト同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/Tag.php";
require_once __DIR__ . "/../lib/Tools.php";
require_once __DIR__ . "/../lib/LogClass.php";

$Tag = new Tag($loginInfo);
$Log = new LogClass(__FILE__);
#センサーに格納された各タグの設定が変更されたとされる時刻リストの取得
$lastUpdateList = $Tag->getLastTagUpdateTime();
$Log->Systemlog("各タグ設定時刻情報送信", $lastUpdateList);
$resStr = postCurl("http://" . URL . "/SyncAPI/getTagUpdate.php", json_encode($lastUpdateList));
$Log->Systemlog("各タグ設定受信", $resStr);

$resArr = json_decode($resStr, true);
if(!is_null($resArr)) {
    # タグリストのうち,変更と新規追加があった場合はこちらで処理される.

    foreach ($resArr["change"] as $tagInfo) {
        $Tag->setTag($tagInfo);
    }

    # タグリストのうち、削除があった場合はこちらで処理される。
    foreach ($resArr["delete"] as $tagId) {
        $Tag->delTag($tagId);
    }
}