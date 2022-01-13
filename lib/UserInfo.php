<?php

use Symfony\Component\Cache\Simple\PdoCache;

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";

class UserInfo
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
            var_dump(password_verify($password,$userAuthObj->fetch()["passwd"]));
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
        if (!$this->viewConfigExist($userId)) {
            return false;
        }
        $setAllWeekCfgSql = "UPDATE viewConfig SET startTime = :startTime,endTime = :endTime WHERE userId = :userId";
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
        if (!$this->viewConfigExist($userId)) {
            return false;
        }

        if ($value) {
            $pubView = 1;
        } else {
            $pubView = 0;
        }

        $setPubViewCfgSql = "UPDATE viewConfig SET publicView = :value WHERE useId = :userId AND weekNum = :weekNum";
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

    public function getViewConfig($userId)
    {
        if (!$this->viewConfigExist($userId)) {
            return false;
        }

        $getViewConfigSql = "SELECT weekNum,startTime,endTime,publicView FROM viewConfig WHERE userId =:userId";
        try {
            $getViewConfigObj = $this->dbh->prepare($getViewConfigSql);
            $getViewConfigObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getViewConfigObj->execute();
            return $getViewConfigObj->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
        } catch (PDOException $e) {
        }
    }
}
