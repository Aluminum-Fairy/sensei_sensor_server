<?php

require_once(__DIR__ . "/../../../config/Config.php");

use ReCaptcha\RequestMethod;

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require(__DIR__ . "/getUserList.php");
}
