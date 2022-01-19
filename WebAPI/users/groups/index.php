<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__ . "/../../../config/Config.php");


preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$groupId = explode('/', $matches[1])[0];


$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require "./getGroupUser.php";
} elseif ($requestMethod === "put") {
    require "./groupEdit.php";
} elseif ($requestMethod == "delete") {
    require "./deleteGroup.php";
}
