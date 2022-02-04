<?php

require __DIR__ . "/../../../lib/UserGroup.php";
require __DIR__ . "/../../../lib/JwtAuth.php";


$UserGroup = new UserGroup($loginInfo);
$JwtAuth = new JwtAuth($loginInfo);

$userId = $JwtAuth->auth();
if (!$userId !== false) {
    $jsonText = file_get_contents("php://input");
    $groupInfo = json_decode($jsonText);
    #グループの更新作業としては
    #1．指定されたグループID一覧削除
    #2．削除後追加
    $UserGroup->beginTransaction();
    if ($UserGroup->editGroup($groupId, $groupInfo["groupName"], $groupInfo["users"])) {
        $UserGroup->commit();
        http_response_code(200);
    } else {
        $UserGroup->rollBack();
        $UserGroup->Systemlog(__FILE__, ROLLBACK_Message);
        http_response_code(500);
    }
}
