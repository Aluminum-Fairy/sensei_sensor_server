<?php

require __DIR__ . "/../../../../lib/UserGroup.php";

$UserGroup = new UserGroup($loginInfo);
$jsonText = file_get_contents("php://input");
$groupInfo = json_decode($jsonText);
#グループの更新作業としては
#1．指定されたグループID一覧削除
#2．削除後追加
$UserGroup->beginTransaction();
if($UserGroup->editGroup($groupId,$groupInfo["groupName"],$groupInfo["users"])){
    $UserGroup->commit();
}else{
    $UserGroup->rollBack();
}