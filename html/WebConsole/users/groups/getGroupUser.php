<?php

require_once(__DIR__ . "/../../../../lib/UserGroup.php");

$UserGroup = new UserGroup($loginInfo);

$result = $UserGroup->getGroupName($groupId);
if($result !== false){
    $result +=array("users"=> $UserGroup->getGroupUser($groupId));
    echo json_encode($result);
}else{
    http_response_code(500);
}
