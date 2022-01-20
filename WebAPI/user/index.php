<?php

require_once(__DIR__ . "/../../config/Config.php");
require_once(__DIR__ . "/../../lib/JwtAuth.php");

use ReCaptcha\RequestMethod;

preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require(__DIR__ . "/getUserInfo.php");
}
