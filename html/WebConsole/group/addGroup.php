<?php

require(__DIR__ . "/../../../lib/UserGroup.php");

$UserGroup = new UserGroup($loginInfo);

$groupName=filter_input(INPUT_POST,"groupName");

if($groupName === false){
    http_response_code(400);
}

if($UserGroup->addUserGroup($groupName)){
    http_response_code(200);
    $UserGroup->getGroupList();
}else{
    http_response_code(500);
}