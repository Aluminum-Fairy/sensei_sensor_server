<?php

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";
require_once __DIR__ . "/Weeks.php";
require_once __DIR__ . "/LogTrait.php";

class UserInfo extends Weeks
{
    use LogTrait;
    use Verify;

    protected PDO $dbh;

    public function __construct($loginInfo)
        //初期化時にデータベースへの接続
    {
        try {
            $this->dbh = new PDO($loginInfo[0], $loginInfo[1], $loginInfo[2], array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
        $addUserSql = "INSERT INTO user (userName,passwd,description) VALUES (:userName,:password,:description)";
        try {
            $addUserObj = $this->dbh->prepare($addUserSql);
            $addUserObj->bindValue(":userName", htmlspecialchars($userName), PDO::PARAM_STR);
            $addUserObj->bindValue(":password", password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $addUserObj->bindValue(":description", htmlspecialchars($description), PDO::PARAM_STR);
            $addUserObj->execute();
            return true;
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $this->Systemlog(__FUNCTION__, $e->getMessage());
        }
        return false;
    }

    public function getLastUserUpdateTime()
    {
        $getLastUserUpdateTimeSql = "SELECT userId,updateTime FROM user";
        try {
            $getLastUserUpdateTimeObj = $this->dbh->prepare($getLastUserUpdateTimeSql);
            $getLastUserUpdateTimeObj->execute();
            return $getLastUserUpdateTimeObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
        }
        return false;
    }

    public function getUserInfo($userId)
    {
        if (!$this->userExist($userId)) {
            return false;
        }

        $getUserInfoSql = "SELECT userId,userName , description,updateTime FROM user WHERE userId = :userId";
        try {
            $getUserInfoObj = $this->dbh->prepare($getUserInfoSql);
            $getUserInfoObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getUserInfoObj->execute();
            return $getUserInfoObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
        }
        return false;
    }

    public function setUser($userInfo)
    {
        if ($this->userExist($userInfo["userId"])) {
            $setUserSql = "UPDATE user SET userName = :userName ,description = :description,updateTime = :updateTime WHERE userId = :userId";
        } else {
            $setUserSql = "INSERT INTO user (userId,userName,description,updateTime) VALUES  (:userId,:userName,:description,:updateTime)";
        }

        try {
            $setUserObj = $this->dbh->prepare($setUserSql);
            $setUserObj->bindValue(":userId", $userInfo["userId"], PDO::PARAM_INT);
            $setUserObj->bindValue(":userName", htmlspecialchars($userInfo["userName"]), PDO::PARAM_STR);
            $setUserObj->bindValue(":description", htmlspecialchars($userInfo["description"]), PDO::PARAM_STR);
            $setUserObj->bindValue(":updateTime", $userInfo["updateTime"], PDO::PARAM_INT);
            $setUserObj->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            $this->Systemlog(__FUNCTION__, $e->getMessage());
            exit();
        }

        return true;
    }

    public function checkUserUpdate($userInfo)
    {
        #センサのアップデート確認
        if (!$this->userExist($userInfo["userId"])) {
            return false;
        }
        $getUserUpdateSql = "SELECT userId,userName,description,updateTime FROM user WHERE updateTime >:updateTime AND userId = :userId";
        try {
            $getUserUpdateObj = $this->dbh->prepare($getUserUpdateSql);
            $getUserUpdateObj->bindValue(":updateTime", $userInfo["updateTime"], PDO::PARAM_STR);
            $getUserUpdateObj->bindValue(":userId", $userInfo["userId"], PDO::PARAM_INT);
            $getUserUpdateObj->execute();
            return $getUserUpdateObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
                $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $setAllWeekCfgObj->bindValue(":startTime", $startTime , PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":endTime", $endTime , PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":userId", $userId , PDO::PARAM_INT);
            $setAllWeekCfgObj->execute();
            return true;
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
        }
        return false;
    }

    public function setPubViewTimeCfg($userId, $weekNum, $publicMode)
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

        $setPubViewCfgSql = "UPDATE viewTimeConfig SET publicView = :value WHERE userId = :userId AND weekNum = :weekNum";
        try {
            $setPubViewCfgObj = $this->dbh->prepare($setPubViewCfgSql);
            $setPubViewCfgObj->bindValue(":value", $pubView, PDO::PARAM_INT);
            $setPubViewCfgObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $setPubViewCfgObj->bindValue(":weekNum", $weekNum, PDO::PARAM_INT);
            $setPubViewCfgObj->execute();
            return true;
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
                    array_push($public, array("roomId" => $SensorConfig["roomId"], "roomName" => $SensorConfig["roomName"]));
                } else {
                    array_push($private, array("roomId" => $SensorConfig["roomId"], "roomName" => $SensorConfig["roomName"]));
                }
            }
            return array("publicationPlace" => array("public" => $public, "private" => $private));
        } catch (PDOException $e) {
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $this->Systemlog(__FUNCTION__, $e->getMessage());
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
            $this->Systemlog(__FUNCTION__, $e->getMessage());
        }
        return false;
    }
}
