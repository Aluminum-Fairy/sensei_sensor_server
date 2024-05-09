<?php

use PhpMyAdmin\Utils\HttpRequest;

require __DIR__ . "/../../../lib/UserInfo.php";
require __DIR__ . "/../../../lib/JwtAuth.php";

$UserInfo = new UserInfo($loginInfo);
$JwtAuth = new JwtAuth($loginInfo);

$userId = $JwtAuth->auth();

if ($userId !== false) {
    $userList = $UserInfo->getUserListGroupByCourse();
    $userListArray = array();
    foreach ($userList as $userInfo) {
        $courseId = $userInfo["courseId"];
        if (!array_key_exists($courseId, $userListArray)) {
            $userListArray += array($courseId => array());
            $userListArray[$courseId] += array("courseName" => $userInfo["courseName"]);
            $userListArray[$courseId] += array("users" => array());
        }
        $userListArray[$courseId]["users"][] = array("userId" => $userInfo["userId"], "userName" => $userInfo["userName"]);
    }
    $result = array();
    $tmpInfo = array();
    foreach ($userListArray as $courseId => $courseInfo) {
        $tmpInfo = array_merge(array("courseId" => $courseId), $courseInfo);
        $result[] = $tmpInfo;
    }
    echo json_encode($result);
}
