<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__ . "/../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../config/Config.php");

$JWT = new JwtAuth($loginInfo);

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
if ($requestMethod === "options") {
    exit();
}

$JWT->checkLogin();
