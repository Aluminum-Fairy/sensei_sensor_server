<?php

require_once(__DIR__ . "/../../../lib/UserGroup.php");
$UserGroup = new UserGroup($loginInfo);

$result = $UserGroup->getGroupName($groupId);

var_dump($result);