<?php

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/UserInfo.php";

$UserInfo = new UserInfo($loginInfo);

#POSTで送信されたデータ
$json = file_get_contents("php://input");
$userInfoArr = json_decode($json, true);

#結果を入れる配列の準備
$result = array("change" => array());

#新たに追加されたセンサーをusrIdから検索、$result["change"]に格納
$userListFromDB = $UserInfo->getUserIdList();
$userListFromReq = array();
foreach ($userInfoArr AS $userInfo) {
    $userListFromReq[] = $userInfo;
}
$newUserIdArr = array_diff($userListFromDB,$userListFromReq);
foreach ($newUserIdArr AS $newUserId){
    $result["change"][] = $UserInfo->getUserInfo($newUserId);
}

#センサーの更新時間とuserIdから変更点を検索
#削除された場合checkSensorUpdate()は0を返す
foreach ($userInfoArr as $userInfo) {
    $res = $UserInfo->checkUserUpdate($userInfo);
    if (false === $res) {
    }  else {
        $result["change"][] = $res;
    }
}

echo json_encode($result);