<?php

require_once(__DIR__ . "/../../../config/Config.php");

use ReCaptcha\RequestMethod;

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);

if ($requestMethod === "options") {
    exit();
} elseif ($requestMethod === "get") {
    require(__DIR__ . "/getViewPlace.php");
} elseif ($requestMethod === "put") {
    require(__DIR__ . "/changePubPlace.php");
}
