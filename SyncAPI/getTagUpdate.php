<?php

require_once __DIR__ ."/../config/Config.php";
require_once __DIR__ ."/../lib/Tag.php";

$Tag = new Tag($loginInfo);

#POSTで送信されたデータ
$json = file_get_contents("php://input");
$tagInfoArr = json_decode($json, true);

#結果を入れる配列の準備
$result = array("change" => array());
$result += array("delete" => array());

#新たに追加されたタグをタグIDから検索,$result["change"]に格納
$tagListFRomDB = $Tag->getTagIdList();

$tagListFromReq = array();
foreach ($tagInfoArr as $tagInfo) {
    $tagListFromReq[] = $tagInfo["tagId"];
}

$newTagIdArr = array_diff($tagListFRomDB,$tagListFromReq);
//var_dump($newTagIdArr);
foreach ($newTagIdArr as $newTagId){
    $result["change"][] = $Tag->getTagInfo($newTagId);
}
#タグの更新時間とIDから変更点を検索
foreach ($tagInfoArr as $tagInfo) {
    $res = $Tag->checkTagUpdate($tagInfo);
    if (false === $res) {
    } elseif ($res === 0) {
        $result["delete"][] = $tagInfo["tagId"];
    } else {
        $result["change"][] = $res;
    }
}

print(json_encode($result));