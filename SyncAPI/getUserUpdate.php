<?php

require_once __DIR__ ."/../config/Config.php";
require_once __DIR__ ."/../lib/UserInfo.php";

$UserInfo = new UserInfo($loginInfo);

#POSTで送信されたデータ
$json = file_get_contents("php://input");
$userInfoArr = json_decode($json, true);

#結果を入れる配列の準備
$result = array("change" => array());

#新たに追加されたユーザーをユーザーIDから検索、$result["change"]に格納
$userListFromDB = $UserInfo->getUserIdList();

$userListFromReq = array();
foreach ($userInfoArr as $userInfo) {
    $userListFromReq[] = $userInfo["userId"];
}

$newUserIdArr = array_diff($userListFromDB, $userListFromReq);             #ユーザーリストの比較、無いIDだけピックアップ
foreach ($newUserIdArr as $newUserId) {
    $result["change"][] = $UserInfo->getUserInfo($newUserId);
}

#センサーの更新時間とIDから変更点を検索
#削除された場合checkUserUpdate()は0を返す
foreach ($userInfoArr as $userInfo) {
    $res = $UserInfo->checkUserUpdate($userInfo);
    if (false === $res) {
    } else {
        $result["change"][] = $res;
    }
}

print(json_encode($result));