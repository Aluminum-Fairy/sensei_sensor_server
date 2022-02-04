<?php

require(__DIR__ . "/../../../lib/UserGroup.php");
require_once(__DIR__ . "/../../../lib/JwtAuth.php");

$UserGroup = new UserGroup($loginInfo);
$JWT = new JwtAuth($loginInfo);
$userId = $JWT->auth();

if ($userId !== false) {
    $result = json_encode($UserGroup->getGroupList($userId));
    if ($result === false) {
        http_response_code(500);
    } else {
        http_response_code(200);
        echo $result;
    }
}
