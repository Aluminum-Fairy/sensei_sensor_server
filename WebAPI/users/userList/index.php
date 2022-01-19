<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__ . "/../../../config/Config.php");

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require(__DIR__ . "/getUserList.php");
}
