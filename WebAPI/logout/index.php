<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__ . "/../../../lib/JwtAuth.php");
require_once(__DIR__ . "/../../../config/Config.php");

$JWT = new JwtAuth($loginInfo);

if ($JWT->auth() && $JWT->logout()) {
    http_response_code(200);
} else {
    http_response_code(401);
}
