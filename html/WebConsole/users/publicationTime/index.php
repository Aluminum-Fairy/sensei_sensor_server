<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__."/../../../../config/Config.php");

$requestMethod=strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "get") {
    require(__DIR__."/getViewTime.php");
} elseif ($requestMethod ==="put") {
    require(__DIR__ . "/changePubTime.php");
}
