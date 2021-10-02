<?php
require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__."/Define.php";
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

    public function setSensor($sensorInfo)
    #センサーをDB追加するための関数センサーID(int)と場所の名前(String)とマスターとして稼働するかどうか(intで0,1)、Webサーバー機能を搭載しているかどうか(intで0,1)
    #XSS対策として$placeNameはhtmlspecialcharsを使ってスクリプト挿入対策をしている
    {
        if ($this->sensorExit(($sensorInfo["sensorId"]))) {
            $setSensorSql = "UPDATE sensor SET placeName = :placeName,isMaster = :isMaster,isWebServer = :isWebServer ,updateTime=:updateTime WHERE sensorId =:sensorId";
        }else{
            $setSensorSql = "INSERT INTO sensor (sensorId,placeName,isMaster, isWebServer) VALUES (:sensorId,:placeName,:isMaster,:isWebServer)";
        }

        try {
            $setSensorObj = $this->dbh->prepare($setSensorSql);
            $setSensorObj->bindValue(":sensorId", $sensorInfo["sensorId"], PDO::PARAM_INT);
            $setSensorObj->bindValue(":placeName",  htmlspecialchars($sensorInfo["placeName"]), PDO::PARAM_STR);
            $setSensorObj->bindValue(":isMaster", $sensorInfo["isMaster"], PDO::PARAM_INT);
            $setSensorObj->bindValue(":isWebServer", $sensorInfo["isWebServer"], PDO::PARAM_INT);
            if ($this->sensorExit(($sensorInfo["sensorId"]))) {
                $setSensorObj->bindValue(":updateTime",$sensorInfo["updateTime"]);
            }
            $setSensorObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
        return true;
    }

    public function getSensorInfo($sensorId){
        if (!$this->sensorExit($sensorId)) {
            return false;
        }

        $getSensorInfoSql = "SELECT * FROM sensor WHERE sensorId = :sensorId";
        try{
            $getSensorInfoObj = $this->dbh->prepare($getSensorInfoSql);
            $getSensorInfoObj->bindValue(":sensorId",$sensorId,PDO::PARAM_INT);
            $getSensorInfoObj->execute();
            return $getSensorInfoObj->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){

        }
    }

    public function deleteSenor($sensorId){
        $deleteSensorSql = "DELETE FROM `sensor` WHERE sensorId = :sensorId";
        try{
            $deleteSensorObj = $this->dbh->prepare($deleteSensorSql);
            $deleteSensorObj->bindValue(":sensorId",$sensorId,PDO::PARAM_INT);
            $deleteSensorObj->execute();
        }catch(PDOException $e){

        }
    }

    public function checkSensorUpdate($sensorInfo){
        #センサのアップデート確認
        if(!$this->sensorExit($sensorInfo["sensorId"])){
            return 0;
        }
        $getSensorUpdateSql = "SELECT * FROM sensor WHERE updateTime >:updateTime AND sensorId = :sensorId";
        try{
            $getSensorUpdateObj = $this->dbh->prepare($getSensorUpdateSql);
            $getSensorUpdateObj->bindValue(":updateTime",$sensorInfo["updateTime"],PDO::PARAM_STR);
            $getSensorUpdateObj->bindValue(":sensorId", $sensorInfo["sensorId"], PDO::PARAM_STR);
            $getSensorUpdateObj->execute();
            return $getSensorUpdateObj->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){

        }
    }

    public function getLastSensorUpdateTime(){
        $getLastSensorUpdateTimeSql ="SELECT sensorId,updateTime FROM sensor";
        try{
            $getLastSensorUpdateTimeObj = $this->dbh->prepare($getLastSensorUpdateTimeSql);
            $getLastSensorUpdateTimeObj->execute();
            return $getLastSensorUpdateTimeObj->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){

        }
    }

    public function getSensorIdList(){
        $getSensorIdListSql = "SELECT sensorId FROM sensor";
        try{
            $getSensorIdListObj = $this->dbh->prepare($getSensorIdListSql);
            $getSensorIdListObj->execute();
            return $getSensorIdListObj->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){

        }
    }

    public function getLastLogTime($sensorId,$for)
    #各センサーの最後の更新時間
    #forは誰が使うかを書く
    {
        if($for == MATCH){
            $getLLTSql = "SELECT sensor.sensorId,ifnull(max(time),0) as time FROM discoveryLog RIGHT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId WHERE sensor.sensorId = :sensorId GROUP BY sensorId";
        }else{
            $getLLTSql = "SELECT sensor.sensorId,ifnull(max(time),0) as time FROM discoveryLog RIGHT JOIN sensor ON discoveryLog.sensorId = sensor.sensorId WHERE sensor.sensorId != :sensorId GROUP BY sensorId";

        }
        try {
            $getLLTObj = $this->dbh->prepare($getLLTSql);
            $getLLTObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $getLLTObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
        }
        return $getLLTObj->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiscvLog($sensorId, $time,$for)
    #センサーの検出情報を一括で読み出す関数(特定時刻以降を指定する)
    #forは誰が使うかを書く
    {
        if($for == EXCLUSION){
            $getDiscvLogSql = "SELECT max(time)as time,sensorId,userId FROM discoveryLog WHERE time >:time AND sensorId != :sensorId GROUP by sensorId,userId;";
        }elseif($for == MATCH){
            $getDiscvLogSql = "SELECT max(time)as time,sensorId,userId FROM discoveryLog WHERE time >:time AND sensorId = :sensorId GROUP by sensorId,userId;";
        }else{
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
            header("Error:" . $e);
            exit();
        }
        return $getDiscvLogObj->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inputDiscvLog($time, $sensorId, $userId)
    #センサの検出情報をDBに取り込む
    {
        if (!$this->sensorExit(($sensorId))) {
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
            header("Error:" . $e);
            return false;
        }
    }
}
