<?php

##センサー側のタグリスト同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/Tag.php";
require_once __DIR__ . "/../lib/Tools.php";

$Tag = new Tag($loginInfo);

#センサーに格納された各タグの設定が変更されたとされる時刻リストの取得
$lastUpdateList = $Tag->getLastTagUpdateTime();
$resStr = postCurl("http://" . URL . "/SyncAPI/getTagUpdate.php", json_encode($lastUpdateList));
$resArr = json_decode($resStr, true);
# タグリストのうち,変更と新規追加があった場合はこちらで処理される.

foreach ($resArr["change"] as $tagInfo) {
    $Tag->setTag($tagInfo);
}

# タグリストのうち、削除があった場合はこちらで処理される。
foreach ($resArr["delete"] as $tagId) {
    $Tag->delTag($tagId);
}
