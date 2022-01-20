<?php
require_once __DIR__."/../lib/Tools.php";

$result =  postCurl("localhost/sensei-sensor-php/WebAPI/groups/",'{
  "groupId": [
    1,
    2,
    3
  ]
}');

var_dump(json_decode($result));