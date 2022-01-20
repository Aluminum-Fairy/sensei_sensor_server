<?php

require_once(__DIR__ . "/../../config/Config.php");
require_once(__DIR__ . "/../../lib/UserGroup.php");


use ReCaptcha\RequestMethod;
$UserGroup = new UserGroup($loginInfo);


preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$groupId = explode('/', $matches[1])[0];


$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
}
