<?php
require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
class Sensor
{
    protected $dbh;
    use Verify;

    function __construct($loginInfo)
    //初期化時にデータベースへの接続
    {
        try {
            $this->dbh = new PDO($loginInfo[0], $loginInfo[1], $loginInfo[2], array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
    }

    public function addSensor($sensorId, $placeName, $isMaster, $isWebServer)
    #センサーをDB追加するための関数センサーID(int)と場所の名前(String)とマスターとして稼働するかどうか(intで0,1)、Webサーバー機能を搭載しているかどうか(intで0,1)
    #XSS対策として$placeNameはhtmlspecialcharsを使ってスクリプト挿入対策をしている
    {
        if ($this->sensorExit(($sensorId))) {
            return false;
        }
        $addSensorSql = "INSERT INTO sensor (sensorId,placeName,isMaster, isWebServer) VALUES (:sensorId,:placeName,:isMaster,:isWebServer)";
        try {
            $addSensorObj = $this->dbh->prepare($addSensorSql);
            $addSensorObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $addSensorObj->bindValue(":placeName",  htmlspecialchars($placeName), PDO::PARAM_STR);
            $addSensorObj->bindValue(":isMaster", $isMaster, PDO::PARAM_INT);
            $addSensorObj->bindValue(":isWebServer", $isWebServer, PDO::PARAM_INT);
            $addSensorObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
        return true;
    }

    public function getDiscvLogHost()
    #センサーの検出情報を一括で読み出す関数(ホストが使うため、すべてのセンサーでの検出情報を送信する)
    {
        try {
            $getDiscvLogSql = "SELECT
            CASE WHEN FLOOR(MIN((TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, time)))/180)) > 511
                THEN 511
                ELSE FLOOR(MIN((TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, time)))/180))
                END as time,
            sensorId, userId
            FROM `discoveryLog` GROUP BY userId ORDER BY time ASC;";

            $getDiscvLogObj = $this->dbh->prepare($getDiscvLogSql);
            $getDiscvLogObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
        return $getDiscvLogObj->fetch(PDO::FETCH_ASSOC);
    }

    public function getDiscvLogSensor($sensorId)
    #センサーの検出情報を一括で読み出す\関数(センサーがホストへ送信するときに使う関数)
    {
        if (!$this->sensorExit(($sensorId))) {
            return false;
        }
        try {
            $getDiscvLogSql = "SELECT
            CASE WHEN FLOOR(MIN((TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, time)))/180)) > 511 \
                THEN 511
                ELSE FLOOR(MIN((TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, time)))/180))\
                END as time,
            sensorId, userId
            FROM `discoveryLog` WHERE sensorId = :sensorId GROUP BY userId ORDER BY time ASC;";

            $getDiscvLogObj = $this->dbh->prepare($getDiscvLogSql);
            $getDiscvLogObj->bindValue(":sensorId",$sensorId,PDO::PARAM_INT);
            $getDiscvLogObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
        return $getDiscvLogObj->fetch(PDO::FETCH_ASSOC);
    }
}
