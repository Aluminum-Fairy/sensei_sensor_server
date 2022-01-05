<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__."/../../../lib/JwtAuth.php");
require_once(__DIR__."/../../../config/Config.php");


preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);

$JWT = new JwtAuth($loginInfo);

$JWT->login();