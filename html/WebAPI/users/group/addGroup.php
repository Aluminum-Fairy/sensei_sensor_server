<?php

require(__DIR__ . "/../../../../lib/UserGroup.php");
header('Content-Type: application/json');

$UserGroup = new UserGroup($loginInfo);

$groupName=filter_input(INPUT_POST, "groupName");

if ($groupName === false) {
    http_response_code(400);
}

if ($UserGroup->addUserGroup($groupName)) {
    $result = json_encode($UserGroup->getGroupList());
    if ($result === false) {
        http_response_code(500);
    } else {
        http_response_code(200);
        echo $result;
    }
} else {
    http_response_code(500);
}
