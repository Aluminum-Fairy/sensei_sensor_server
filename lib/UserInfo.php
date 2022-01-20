<?php

use Symfony\Component\Cache\Simple\PdoCache;

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";
require_once __DIR__ . "/Weeks.php";

class UserInfo extends Weeks
{
    use Verify;

    protected $dbh;

    public function __construct($loginInfo)
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

    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }

    public function commit()
    {
        $this->dbh->commit();
    }

    public function rollBack()
    {
        $this->dbh->rollBack();
    }


    public function addUser($userName, $password, $description)
    {
        $addUserSql = "INSERT INTO user (userName,password,description) VALUES (:userName,:password,:description)";
        try {
            $addUserObj = $this->dbh->prepare($addUserSql);
            $addUserObj->bindValue(":userName", htmlspecialchars($userName), PDO::PARAM_STR);
            $addUserObj->bindValue(":password", password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $addUserObj->bindValue(":description", htmlspecialchars($description), PDO::PARAM_STR);
            $addUserObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function chPasswd($userId, $oldPasswd, $newPasswd)
    {
        if (!$this->userAuth($userId, $oldPasswd)) {
            return false;
        }

        $chPasswdSql = "UPDATE user SET passwd = :passwd WHERE userId = :userId";
        try {
            $chPasswdObj = $this->dbh->prepare($chPasswdSql);
            $chPasswdObj->bindValue(":passwd", password_hash($newPasswd, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $chPasswdObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function userAuth($userId, $password)
    {
        if (!$this->userExist($userId)) {
            return false;
        }

        $userAuthSql = "SELECT passwd FROM user WHERE userId = :userId";
        try {
            $userAuthObj = $this->dbh->prepare($userAuthSql);
            $userAuthObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $userAuthObj->execute();
            return password_verify($password, $userAuthObj->fetch()["passwd"]);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function setSensorsUser($userId,$userName,$description,$updateTime){
        if($this->userExist($userId)){
            $setSensorsUserSql = "UPDATE user SET userName = :userName, description = :description,updateTime = :updateTime WHERE userId = :userId";
        }else{
            $setSensorsUserSql = "INSERT INTO user (userId,userName,description,updateTime) VALUES (:userId,:userName,:description,updateTime)";
        }

        try{
            $setSensorsUserObj = $this->dbh->prepare($setSensorsUserSql);
            $setSensorsUserObj->bindValue(":userName",$userName,PDO::PARAM_STR);
            $setSensorsUserObj->bindValue(":userId",$userId,PDO::PARAM_INT);
            $setSensorsUserObj->bindValue(":description",$description,PDO::PARAM_STR);
            $setSensorsUserObj->bindValue(":updateTime",$updateTime,PDO::PARAM_STR);
            $setSensorsUserObj->execute();
            return false;
        }catch(PDOException $e) {
            http_response_code(500);
            header("Error:" . $e);
            exit();
        }
    }

    public function getUserInfo()
        #引数なしではすべてのユーザーリストを返す,引数を1つ渡すとそのユーザーを検索して1件返す(存在しなければFalse)
    {
        $args = func_num_args();
        if($args == 1){
            $userId = func_get_arg()[0];
            if ($this->userExist($userId)) {
                return false;
            }
            $getUserInfoSql = "SELECT userName , description ,updateTime FROM user WHERE userId = :userId";
        }else{
            $getUserInfoSql = "SELECT userName , description ,updateTime FROM user";
        }

        try {
            $getUserInfoObj = $this->dbh->prepare($getUserInfoSql);
            if($args == 1){
                $getUserInfoObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            }
            $getUserInfoObj->execute();
            return $getUserInfoObj->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function checkUserUpdate($userId,$updateTime){
        if(!$this->userExist($userId)){
            return false;
        }

        $checkUserUpdateSql = "SELECT userName , description ,updateTime FROM user WHERE updateTime > :updateTime AND userId = :userId";
        try{
            $checkUserUpdateObj = $this->dbh->prepare($checkUserUpdateSql);
            $checkUserUpdateObj->bindValue("updateTime",$updateTime,PDO::PARAM_STR);
            $checkUserUpdateObj->bindValue(":userId",$userId,PDO::PARAM_INT);
            $checkUserUpdateObj->execute();
            return $checkUserUpdateObj->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $e){

        }
        return false;
    }

    public function getLastUserUpdatTime()
    {
        $getLastUserUpdateTimeSql = "SELECT sensorId,updateTime FROM sensor";
        try {
            $getLastUserUpdateTimeObj = $this->dbh->prepare($getLastUserUpdateTimeSql);
            $getLastUserUpdateTimeObj->execute();
            return $getLastUserUpdateTimeObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {

        }
        return false;
    }

    public function getUserIdList()
    {
        $getUserIdListSql = "SELECT userId FROM user";
        try {
            $getUserIdListObj = $this->dbh->prepare($getUserIdListSql);
            $getUserIdListObj->execute();
            return $getUserIdListObj->fetchAll(PDO::FETCH_COLUMN);
        }catch(PDOException $e){

        }
        return false;
    }




    protected function genWeekCfg($userId)
    {
        $genWeekCfgSql = "INSERT INTO viewTimeConfig (`userId`, `weekNum`, `startTime`, `endTime`, `publicView`) VALUES (:userId,:weekNum,:startTime,:endTime,:publicView)";
        for ($weekNum = 0; $weekNum <= 6; $weekNum++) {
            try {
                $genWeekCfgObj = $this->dbh->prepare($genWeekCfgSql);
                $genWeekCfgObj->bindValue(":userId", $userId, PDO::PARAM_INT);
                $genWeekCfgObj->bindValue(":weekNum", $weekNum, PDO::PARAM_INT);
                $genWeekCfgObj->bindValue(":startTime", DEFAULT_STAR_TIMR, PDO::PARAM_INT);
                $genWeekCfgObj->bindValue(":endTime", DEFAULT_END_TIMR, PDO::PARAM_INT);
                $genWeekCfgObj->bindValue(":publicView", DEFAULT_PUBLIC_CONFIG, PDO::PARAM_INT);
                $genWeekCfgObj->execute();
            } catch (PDOException $e) {
                return false;
            }
        }
        return true;
    }


    public function setAllWeekCfg($userId, $startTime, $endTime)
        #全ての登録済みの曜日における公開時間を設定する
    {
        if (!$this->viewTimeConfigExist($userId)) {
            return false;
        }
        $setAllWeekCfgSql = "UPDATE viewTimeConfig SET startTime = :startTime,endTime = :endTime WHERE userId = :userId";
        try {
            $setAllWeekCfgObj = $this->dbh->prepare($setAllWeekCfgSql);
            $setAllWeekCfgObj->bindValue(":startTime", $startTime . PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":endTime", $endTime . PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":userId", $userId . PDO::PARAM_INT);
            $setAllWeekCfgObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function setPubViewCfg($userId, $weekNum, $publicMode)
        //曜日ごとに公開時間を変更するための関数.将来のために実装
    {
        if (!$this->viewTimeConfigExist($userId)) {
            return false;
        }

        if ($publicMode) {
            $pubView = 1;
        } else {
            $pubView = 0;
        }

        $setPubViewCfgSql = "UPDATE viewTimeConfig SET publicView = :value WHERE useId = :userId AND weekNum = :weekNum";
        try {
            $setPubViewCfgObj = $this->dbh->prepare($setPubViewCfgSql);
            $setPubViewCfgObj->bindValue(":value", $pubView, PDO::PARAM_INT);
            $setPubViewCfgObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $setPubViewCfgObj->bindValue(":weekNum", $weekNum, PDO::PARAM_INT);
            $setPubViewCfgObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function setPubPlaceCfg($userId, $sensorId, $publicMode)
    {
        if (!$this->viewSensorConfigExist($userId)) {
            return false;
        }

        if ($publicMode) {
            $pubView = 1;
        } else {
            $pubView = 0;
        }

        $setPubPlaceCfgSql = "UPDATE viewSensorConfig SET publicView = :publicView WHERE userId = :userId AND sensorId = :sensorId";
        try {
            $setPubPlaceCfgObj = $this->dbh->prepare($setPubPlaceCfgSql);
            $setPubPlaceCfgObj->bindValue(":publicView", $pubView, PDO::PARAM_INT);
            $setPubPlaceCfgObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $setPubPlaceCfgObj->bindValue(":sensorId", $sensorId, PDO::PARAM_INT);
            $setPubPlaceCfgObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getViewSensorConfig($userId)
    {
        if (!$this->viewSensorConfigExist($userId)) {
            return false;
        }
        $getViewSensorConfigSql = "SELECT viewSensorConfig.sensorId AS roomId,sensor.placeName AS roomName,viewSensorConfig.publicView AS publicView FROM viewSensorConfig LEFT JOIN sensor ON viewSensorConfig.sensorId = sensor.sensorId WHERE userId = :userId";
        try {
            $getViewSensorConfigObj = $this->dbh->prepare($getViewSensorConfigSql);
            $getViewSensorConfigObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getViewSensorConfigObj->execute();
            $ViewSensorConfig = $getViewSensorConfigObj->fetchAll(PDO::FETCH_ASSOC);
            $public = array();
            $private = array();
            foreach ($ViewSensorConfig as $SensorConfig) {
                if ($SensorConfig["publicView"] === "1") {
                    $public[] = array("roomId" => $SensorConfig["roomId"], "roomName" => $SensorConfig["roomName"]);
                } else {
                    $private[] = array("roomId" => $SensorConfig["roomId"], "roomName" => $SensorConfig["roomName"]);
                }
            }
            return array("publicationPlace" => array("public" => $public, "private" => $private));
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getViewDays($userId)
    {
        $config = $this->getViewTimeConfig($userId);
        if ($config === false) {
            return false;
        }
        $result = array("publicationDays" => array());
        foreach ($config as $weekNum => $weekConfig) {
            $result["publicationDays"] += array($this->getWeek($weekNum - 1) => $weekConfig["publicView"] == 1);
        }
        return $result;
    }

    public function getViewTimeConfig($userId)
    {
        if (!$this->viewTimeConfigExist($userId)) {
            return false;
        }

        $getViewTimeConfigSql = "SELECT weekNum,startTime,endTime,publicView FROM viewTimeConfig WHERE userId =:userId";
        try {
            $getViewTimeConfigObj = $this->dbh->prepare($getViewTimeConfigSql);
            $getViewTimeConfigObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getViewTimeConfigObj->execute();
            return $getViewTimeConfigObj->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getViewTime($userId)
    {
        $config = $this->getViewTimeConfig($userId);
        if ($config === false) {
            return false;
        }
        $firstWeekNum = array_keys($config)[0];
        $result = array("publicationTime" => array("start" => $config[$firstWeekNum]["startTime"]));
        $result["publicationTime"] += array("end" => $config[$firstWeekNum]["endTime"]);
        return $result;
    }

    public function getUserListGroupByCourse()
    {
        $getUserListSql = "SELECT ifnull(courseList.courseId,0) as courseId, ifnull(courseList.courseName,\"未所属\") AS courseName,user.userId,user.userName 
                                FROM `courseList` 
                                LEFT JOIN courseUserList ON courseList.courseId = courseUserList.courseId 
                                RIGHT JOIN user ON user.userId = courseUserList.userId";

        try {
            $getUserListObj = $this->dbh->prepare($getUserListSql);
            $getUserListObj->execute();
            return $getUserListObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
        return false;
    }
}
