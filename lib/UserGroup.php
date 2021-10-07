<?php
require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";


class UserGroup{
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

    public function addUserGroup($groupName){
        $addUserGroupSql = "INSERT INTO userGroupList (groupName) VALUES (:groupName)";
        try{
            $addUserGroupObj = $this->dbh->prepare($addUserGroupSql);
            $addUserGroupObj->bindValue(":groupName",$groupName,PDO::PARAM_STR);
            $addUserGroupObj->execute();
            return true;
        }catch(PDOException $e){
            return false;
        }
    }

    public function regUser2Group($userId,$groupId){
        if(!$this->userExist($userId) || !$this->groupIdExist($groupId)){
            return false;
        }

        $regUser2GroupSql = "INSERT INTO userGroup (groupId,userId)VALUES(:groupId,:userId)";
    }
}