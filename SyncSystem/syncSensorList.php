<?php

#センサー側のセンサーリスト同期システム

require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/../lib/Sensor.php";
require_once __DIR__ . "/../lib/Tools.php";
require_once __DIR__ . "/../config/SensorConfig.php";
require_once __DIR__ . "/../lib/LogClass.php";

$Sensor = new Sensor($loginInfo);
$Log = new LogClass(__FILE__);
#センサーに格納された各センサーの設定が変更されたとされる時刻リストを取得
$lastUpdateList = $Sensor->getLastSensorUpdateTime();
$Log->Systemlog("各センサー設定時刻情報送信", $lastUpdateList);

$resStr = postCurl("http://" . URL . "/SyncAPI/getSensorUpdate.php", json_encode($lastUpdateList));
$Log->Systemlog("各センサー設定情報受信", $resStr);
$resArr = json_decode($resStr, true);
if (!is_null($resArr)) {
    # センサーリストのうち、変更と新規追加があった場合はこちらで処理される。
    foreach ($resArr["change"] as $sensorInfo) {
        $Sensor->setSensor($sensorInfo);
    }

    # センサーリストのうち、削除があった場合はこちらで処理される。
    foreach ($resArr["delete"] as $sensorId) {
        $Sensor->deleteSenor($sensorId);
    }
}
