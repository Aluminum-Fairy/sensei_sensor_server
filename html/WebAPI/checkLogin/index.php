<?php

use ReCaptcha\RequestMethod;

require_once(__DIR__."/../../../lib/JwtAuth.php");
require_once(__DIR__."/../../../config/Config.php");

$JWT = new JwtAuth($loginInfo);

$JWT->checkLogin();
