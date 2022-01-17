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
            return false;
        }
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
            return false;
        }
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
            return false;
        }
    }

    public function getUserInfo($userId)
    {
        if ($this->userExist($userId)) {
            return false;
        }

        $getUserInfoSql = "SELECT userName , description FROM user WHERE userId = :userId";
        try {
            $getUserInfoObj = $this->dbh->prepare($getUserInfoSql);
            $getUserInfoObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getUserInfoObj->execute();
            return $getUserInfoObj->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
        }
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
            $setAllWeekCfgObj->bindValue(":startTime", $startTime.PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":endTime", $endTime . PDO::PARAM_INT);
            $setAllWeekCfgObj->bindValue(":userId", $userId . PDO::PARAM_INT);
            $setAllWeekCfgObj->execute();
            return true;
        } catch (PDOException $e) {
        }
    }

    public function setPubViewCfg($userId, $weekNum, $value)
    {
        if (!$this->viewTimeConfigExist($userId)) {
            return false;
        }

        if ($value) {
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
    }

    public function getViewSensorConfig($userId)
    {
        if (!$this->viewSensorConfigExist($userId)) {
            echo 1;
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
                    array_push($public, array("roomId"=>$SensorConfig["roomId"],"roomName"=>$SensorConfig["roomName"]));
                } else {
                    array_push($private, array("roomId"=>$SensorConfig["roomId"],"roomName"=>$SensorConfig["roomName"]));
                }
            }
            return array("publicationPlace"=>array("public"=>$public , "private"=>$private));
        } catch (PDOException $e) {
        }
    }

    public function getViewDays($userId)
    {
        $config = $this->getViewTimeConfig($userId);
        $result = array("publicationDays"=>array());
        foreach ($config as $weekNum => $weekConfig) {
            $result["publicationDays"] += array($this->getWeek($weekNum-1)=>$weekConfig["publicView"] == 1);
        }
        return $result;
    }

    public function getViewTime($userId)
    {
        $config = $this->getViewTimeConfig($userId);
        $firstWeekNum = array_keys($config)[0];
        $result = array("publicationTime"=>array("start"=>$config[$firstWeekNum]["startTime"]));
        $result["publicationTime"] += array("end"=>$config[$firstWeekNum]["endTime"]);
        return $result;
    }
}
