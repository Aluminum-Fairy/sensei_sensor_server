<?php

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";
require_once __DIR__ . "/LogTrait.php";

class Sensor
{
    use Verify;
    use LogTrait;
    protected PDO $dbh;

    public function __construct($loginInfo)
        //初期化時にデータベースへの接続
    {
        $this->filePath = __FILE__;
        try {
            $this->dbh = new PDO($loginInfo[0], $loginInfo[1], $loginInfo[2], array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__ ,$e);
            exit();
        }
    }

    public function setSensor($sensorInfo)
        #センサーをDB追加するための関数センサーID(int)と場所の名前(String)とマスターとして稼働するかどうか(intで0,1)、Webサーバー機能を搭載しているかどうか(intで0,1)
        #XSS対策として$placeNameはhtmlspecialcharsを使ってスクリプト挿入対策をしている
    {
        if ($this->sensorExist(($sensorInfo["sensorId"]))) {
            $setSensorSql = "UPDATE sensor SET placeName = :placeName,isMaster = :isMaster,isWebServer = :isWebServer ,updateTime=:updateTime WHERE sensorId =:sensorId";
        } else {
            $setSensorSql = "INSERT INTO sensor (sensorId,placeName,isMaster, isWebServer,updateTime) VALUES (:sensorId,:placeName,:isMaster,:isWebServer,:updateTiime)";
        }

        try {
            $setSensorObj = $this->dbh->prepare($setSensorSql);
            $setSensorObj->bindValue(":sensorId", $sensorInfo["sensorId"], PDO::PARAM_INT);
            $setSensorObj->bindValue(":placeName", htmlspecialchars($sensorInfo["placeName"]), PDO::PARAM_STR);
            $setSensorObj->bindValue(":isMaster", $sensorInfo["isMaster"], PDO::PARAM_INT);
            $setSensorObj->bindValue(":isWebServer", $sensorInfo["isWebServer"], PDO::PARAM_INT);
            $setSensorObj->bindValue(":updateTime", $sensorInfo["updateTime"], PDO::PARAM_STR);
            $setSensorObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__ ,$e);
            exit();
        }
        return true;
    }

    public function changeSensorConfig($sensorId, $placeName, $isMaster, $isWebServer)
    {
        if (!$this->sensorExist($sensorId)) {
            return false;
        }

        $changeSensorConfigSql = "UPDATE sensor SET placeName = :placeName,isMaster = :isMaster,isWebServer = :isWebServer WHERE sensorId = :sensorId";
        try {
            $changeSensorConfigObj = $this->dbh->prepare($changeSensorConfigSql);
            $changeSensorConfigObj->bindValue(":placeName", htmlspecialchars($placeName), PDO::PARAM_STR);
            $changeSensorConfigObj->bindValue(":isMaster", $isMaster, PDO::PARAM_INT);
            $changeSensorConfigObj->bindValue(":isWebServer", $isWebServer, PDO::PARAM_INT);
            $changeSensorConfigObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $changeSensorConfigObj->execute();
            return true;
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
        return false;
    }

    public function getSensorInfo($sensorId)
    {
        if (!$this->sensorExist($sensorId)) {
            return false;
        }

        $getSensorInfoSql = "SELECT * FROM sensor WHERE sensorId = :sensorId";
        try {
            $getSensorInfoObj = $this->dbh->prepare($getSensorInfoSql);
            $getSensorInfoObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $getSensorInfoObj->execute();
            return $getSensorInfoObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
        return false;
    }

    public function deleteSenor($sensorId)
    {
        if (!$this->sensorExist($sensorId)) {
            return false;
        }

        $deleteSensorSql = "DELETE FROM `sensor` WHERE sensorId = :sensorId";
        try {
            $deleteSensorObj = $this->dbh->prepare($deleteSensorSql);
            $deleteSensorObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $deleteSensorObj->execute();
            return true;
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
        return false;
    }

    public function checkSensorUpdate($sensorInfo)
    {
        #センサのアップデート確認
        if (!$this->sensorExist($sensorInfo["sensorId"])) {
            return 0;
        }
        $getSensorUpdateSql = "SELECT * FROM sensor WHERE updateTime >:updateTime AND sensorId = :sensorId";
        try {
            $getSensorUpdateObj = $this->dbh->prepare($getSensorUpdateSql);
            $getSensorUpdateObj->bindValue(":updateTime", $sensorInfo["updateTime"], PDO::PARAM_STR);
            $getSensorUpdateObj->bindValue(":sensorId", $sensorInfo["sensorId"], PDO::PARAM_INT);
            $getSensorUpdateObj->execute();
            return $getSensorUpdateObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
        return  false;
    }

    public function getLastSensorUpdateTime()
    {
        $getLastSensorUpdateTimeSql = "SELECT sensorId,updateTime FROM sensor";
        try {
            $getLastSensorUpdateTimeObj = $this->dbh->prepare($getLastSensorUpdateTimeSql);
            $getLastSensorUpdateTimeObj->execute();
            return $getLastSensorUpdateTimeObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }

    public function getSensorIdList()
    {
        $getSensorIdListSql = "SELECT sensorId FROM sensor";
        try {
            $getSensorIdListObj = $this->dbh->prepare($getSensorIdListSql);
            $getSensorIdListObj->execute();
            return $getSensorIdListObj->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }

    public function getLastLogTime($sensorId, $searchConfition)
        #各センサーの最後の更新時間
        #forは誰が使うかを書く
    {
        if ($searchConfition == MATCH) {
                $getLLTSql = "SELECT sensor.sensorId,ifnull(max(time),0) as time FROM discoveryLog RIGHT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId WHERE sensor.sensorId = :sensorId GROUP BY sensorId";
    } else {
        $getLLTSql = "SELECT sensor.sensorId,ifnull(max(time),0) as time FROM discoveryLog RIGHT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId WHERE sensor.sensorId != :sensorId GROUP BY sensorId";
    }
        try {
            $getLLTObj = $this->dbh->prepare($getLLTSql);
            $getLLTObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $getLLTObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__ ,$e);
        }

        return $getLLTObj->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiscvLog($sensorId, $time, $searchConfition)
        #センサーの検出情報を一括で読み出す関数(特定時刻以降を指定する)
        #forは誰が使うかを書く
    {
        if ($searchConfition == EXCLUSION) {
            $getDiscvLogSql = "SELECT max(time)as time,sensorId,userId FROM discoveryLog WHERE time >:time AND sensorId != :sensorId GROUP by sensorId,userId;";
        } elseif ($searchConfition == MATCH) {
                $getDiscvLogSql = "SELECT max(time)as time,sensorId,userId FROM discoveryLog WHERE time >:time AND sensorId = :sensorId GROUP by sensorId,userId;";
    } else {
        header("Error:" . "Option Error");
        exit();
    }
        try {
            $getDiscvLogObj = $this->dbh->prepare($getDiscvLogSql);
            $getDiscvLogObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $getDiscvLogObj->bindValue(":time", $time, PDO::PARAM_STR);
            $getDiscvLogObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__ ,$e);
            exit();
        }
        return $getDiscvLogObj->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inputDiscvLog($time, $sensorId, $userId)
        #センサの検出情報をDBに取り込む
    {
        if (!$this->sensorExist(($sensorId))) {
            return false;
        }
        $inputDiscvLogSql = "INSERT INTO discoveryLog (sensorId,userId,time)VALUES (:sensorId,:userId,:time)";
        try {
            $inputDiscvLogObj = $this->dbh->prepare($inputDiscvLogSql);
            $inputDiscvLogObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $inputDiscvLogObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $inputDiscvLogObj->bindValue(":time", $time, PDO::PARAM_STR);
            $inputDiscvLogObj->execute();
            return true;
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__ ,$e);
            return false;
        }
    }

    public function getNotFoundDiscvList($minutes)
    {
        $getNotFoundDiscvSql =
            "SELECT discvView.placeName as roomName,discvView.userName,convert_tz(discvView.time,'+00:00','+09:00') as  detectionTime FROM
                (
                    SELECT 
                    user.userName,discoveryLog.userId,
                        sensor.placeName,
                        discoveryLog.time,
                        row_number() over (partition by discoveryLog.userId ORDER BY discoveryLog.time DESC) rownum
                    FROM discoveryLog
                    LEFT JOIN user ON discoveryLog.userId = user.userId
                    LEFT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId 
                    ) AS discvView
            WHERE discvView.rownum =1 AND discvView.time < (CURRENT_TIMESTAMP - INTERVAL :minutes MINUTE);";
        /*
        30分以内に検出されなかっ人全員出す
        */

        try {
            $getNFDLObj = $this->dbh->prepare($getNotFoundDiscvSql);
            $getNFDLObj->bindValue(":minutes",$minutes,PDO::PARAM_INT);
            $getNFDLObj->execute();
            return $getNFDLObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }

    public function getAllDiscvList($minutes)
    {
        $getAllDiscvListSql =
            "SELECT discvView.placeName as roomName,discvView.userName,convert_tz(discvView.time,'+00:00','+09:00') as  detectionTime FROM
                (
                    SELECT 
                    user.userName,discoveryLog.userId,
                        sensor.placeName,
                        discoveryLog.time,
                        row_number() over (partition by discoveryLog.userId ORDER BY discoveryLog.time DESC) rownum
                    FROM discoveryLog
                    LEFT JOIN user ON discoveryLog.userId = user.userId
                    LEFT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId 
                    WHERE discoveryLog.time > (CURRENT_TIMESTAMP - INTERVAL :minutes MINUTE)
                    ) AS discvView
            WHERE discvView.rownum =1;";
        /*
        30分以内に検出された人全員出す
        */

        try {
            $getAllDiscvListObj = $this->dbh->prepare($getAllDiscvListSql);
            $getAllDiscvListObj->bindValue(":minutes",$minutes,PDO::PARAM_INT);
            $getAllDiscvListObj->execute();
            return $getAllDiscvListObj->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }

    public function getAllowedDiscvList()
    {
        $getAllowedDiscvListSql =
            "SELECT View.userName,View.placeName as roomName,convert_tz(View.time,'+00:00','+09:00') as detectionTime FROM
            (
                SELECT discvView.placeName,discvView.userId,discvView.userName,discvView.time ,row_number() over (partition by discvView.userId ORDER BY discvView.time DESC) rownum FROM
                (
                    SELECT 
                    user.userName,discoveryLog.userId,
                        sensor.placeName,
                        discoveryLog.time, 
                        viewTimeConfig.weekNum,viewTimeConfig.startTime,viewTimeConfig.endTime,viewTimeConfig.publicView AS publicTimeView,viewSensorConfig.publicView AS publicSensorView, 
                        60*HOUR(convert_tz(discoveryLog.time,'+00:00','+09:00'))+MINUTE(convert_tz(discoveryLog.time,'+00:00','+09:00')) as TimeNum, DAYOFWEEK(convert_tz(discoveryLog.time,'+00:00','+09:00')) as Week
                    FROM `viewTimeConfig`
                    LEFT JOIN discoveryLog ON viewTimeConfig.userId = discoveryLog.userId
                    LEFT JOIN user ON discoveryLog.userId = user.userId
                    LEFT JOIN viewSensorConfig ON discoveryLog.userId = viewSensorConfig.userId AND discoveryLog.sensorId = viewSensorConfig.sensorId
                    LEFT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId ) AS discvView
                WHERE discvView.TimeNum > discvView.startTime AND discvView.TimeNum < discvView.endTime AND discvView.Week = discvView.WeekNum AND discvView.publicTimeView = 1 AND discvView.publicSensorView = 1) as View
            WHERE View.rownum =1;";
        /*
        最初のSELECTでdiscvoryLogとsensorとuserとsensorをJOINさせる、時刻計算用のTimeNumを追加
        次のSELECTでTimeNumから許可されたものを出す
        最後のSELECTで一番新しいことを示すrownumの1のみ取り出す
        */

        try {
            $getADLObj = $this->dbh->prepare($getAllowedDiscvListSql);
            $getADLObj->execute();
            return $getADLObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }

    public function getAllowedGroupUsersDiscvList($groupId)
    {
        $getAllowedGroupUsersDiscvListSql =
            "SELECT View.userName,View.placeName as roomName,convert_tz(View.time,'+00:00','+09:00') as detectionTime FROM
            (
                SELECT discvView.userName,discvView.userId,discvView.placeName,discvView.time ,row_number() over (partition by discvView.userId ORDER BY discvView.time DESC) rownum FROM
                (
                    SELECT 
                        user.userName,discoveryLog.userId,
                        sensor.placeName,
                        discoveryLog.time, 
                        viewTimeConfig.weekNum,viewTimeConfig.startTime,viewTimeConfig.endTime,viewTimeConfig.publicView AS publicTimeView,viewSensorConfig.publicView AS publicSensorView,
                        60*HOUR(convert_tz(discoveryLog.time,'+00:00','+09:00'))+MINUTE(convert_tz(discoveryLog.time,'+00:00','+09:00')) as TimeNum, DAYOFWEEK(convert_tz(discoveryLog.time,'+00:00','+09:00')) as Week,
                        userGroup.groupId 
                    FROM `viewTimeConfig`
                    LEFT JOIN discoveryLog ON viewTimeConfig.userId = discoveryLog.userId
                    LEFT JOIN user ON discoveryLog.userId = user.userId
                    LEFT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId
                    LEFT JOIN viewSensorConfig ON discoveryLog.userId = viewSensorConfig.userId AND discoveryLog.sensorId = viewSensorConfig.sensorId
                    LEFT JOIN userGroup ON discoveryLog.userId = userGroup.userId) AS discvView
                WHERE discvView.TimeNum > discvView.startTime AND discvView.TimeNum < discvView.endTime AND discvView.Week = discvView.WeekNum AND discvView.groupId = :groupId AND discvView.publicTimeView = 1 AND discvView.publicSensorView = 1) as View
            WHERE View.rownum =1;";
        /*
        最初のSELECTでdiscvoryLogとsensorとuserとsensorをJOINさせる、時刻計算用のTimeNumを追加
        次のSELECTでTimeNumから許可されたものを出す
        最後のSELECTで一番新しいことを示すrownumの1のみ取り出す
        */
        try {
            $getAGUDObj = $this->dbh->prepare($getAllowedGroupUsersDiscvListSql);
            $getAGUDObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $getAGUDObj->execute();
            return $getAGUDObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__ ,$e);
        }
    }
}
