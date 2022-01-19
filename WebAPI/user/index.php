<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../config/Config.php");


preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);

$requestMethod=strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "get") {
    require(__DIR__ . "/getUserInfo.php");
}
