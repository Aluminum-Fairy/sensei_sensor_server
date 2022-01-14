<?php

require(__DIR__ . "/../../../../lib/UserGroup.php");
header('Content-Type: application/json');

$UserGroup = new UserGroup($loginInfo);




$result = json_encode($UserGroup->getGroupList());

if ($result === false) {
    http_response_code(500);
} else {
    http_response_code(200);
    echo $result;
}
