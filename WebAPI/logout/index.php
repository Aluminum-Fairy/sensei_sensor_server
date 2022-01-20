<?php

require_once(__DIR__ . "/../../config/Config.php");
require_once(__DIR__ . "/../../lib/JwtAuth.php");

use ReCaptcha\RequestMethod;

$JWT = new JwtAuth($loginInfo);

$requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
if ($requestMethod === "options") {
    exit();
}

if ($JWT->auth() && $JWT->logout()) {
    http_response_code(200);
} else {
    http_response_code(401);
}
