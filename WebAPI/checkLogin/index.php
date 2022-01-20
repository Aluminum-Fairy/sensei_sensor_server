<?php

require_once(__DIR__ . "/../../config/Config.php");
require_once(__DIR__ . "/../../lib/JwtAuth.php");


use ReCaptcha\RequestMethod;

$JWT = new JwtAuth($loginInfo);

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
if ($requestMethod === "options") {
    exit();
}

$JWT->checkLogin();
