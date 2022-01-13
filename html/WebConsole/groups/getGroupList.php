<?php

require_once(__DIR__ . "/../../../lib/UserGroup.php");
$UserGroup = new UserGroup($loginInfo);

echo $UserGroup->getGroupList();
