<?php

require_once __DIR__ . "/../config/DevelopMode.php";

function devHeader($e)
{
    if (!ReleaseMode) {
        header($e);
    }
}

function postCurl($url, $postData)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $resStr =  curl_exec($ch);
    curl_close($ch);
    return $resStr;
}

function convertTime($time)
#XX:XX ([0]:[1]) -> 0 ~1,440
{
    return 60*$time[0] + $time[1];
}
