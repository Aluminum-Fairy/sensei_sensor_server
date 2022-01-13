<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__."/../../../lib/UserGroup.php");
require_once(__DIR__."/../../../config/Config.php");
$UserGroup = new UserGroup($loginInfo);


preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$groupId = explode('/', $matches[1])[0];


$requestMethod=strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "get"){
    require(__DIR__."/getGroupList.php");
}
