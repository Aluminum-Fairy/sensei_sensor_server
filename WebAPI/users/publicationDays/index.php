<?php

require_once(__DIR__ . "/../../../config/Config.php");

use ReCaptcha\RequestMethod;

preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$groupId = explode('/', $matches[1])[0];


$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require(__DIR__ . "/getViewDays.php");
} elseif ($requestMethod === "put") {
    require(__DIR__ . "/changePubDays.php");
}
