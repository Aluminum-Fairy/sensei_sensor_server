<?php

use PhpMyAdmin\Utils\HttpRequest;

require __DIR__ . "/../../../../lib/UserGroup.php";

$UserGroup = new UserGroup($loginInfo);
$UserGroup->beginTransaction();

if ($UserGroup->delAllUserFromGroup($groupId)) {
    if ($UserGroup->delUserGroup($groupId)) {
        http_response_code(200);
        $UserGroup->commit();
    } else {
        http_response_code(500);
        $UserGroup->rollBack();
    }
} else {
    http_response_code(500);
    $UserGroup->rollBack();
}
