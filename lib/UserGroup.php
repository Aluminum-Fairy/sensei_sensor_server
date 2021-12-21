<?php
require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";


class UserGroup
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

    public function addUserGroup($groupName)
    {
        $addUserGroupSql = "INSERT INTO userGroupList (groupName) VALUES (:groupName)";
        try {
            $addUserGroupObj = $this->dbh->prepare($addUserGroupSql);
            $addUserGroupObj->bindValue(":groupName", htmlspecialchars($groupName), PDO::PARAM_STR);
            $addUserGroupObj->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delUserGroup($groupId)
    {
        if (!$this->groupIdExist($groupId) || !$this->groupMemberExist($groupId) || !$this->groupMemberExist($groupId)) {
            return false;
        }



        $delUserGroupSql = "DELETE FROM userGroupList WHERE groupid = :groupId";
        try {
            $delUserGroupObj = $this->dbh->prepare($delUserGroupSql);
            $delUserGroupObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $delUserGroupObj->execute();
        } catch (PDOException $e) {
        }
    }

    public function regUser2Group($userId, $groupId)
    {
        if (!$this->userExist($userId) || !$this->groupIdExist($groupId)) {
            return false;
        }

        if ($this->relatedUserId2GroupExist($userId, $groupId)) {
            return false;
        }

        $regUser2GroupSql = "INSERT INTO userGroup (groupId,userId)VALUES(:groupId,:userId)";
        try {
            $regUser2GroupObj = $this->dbh->prepare($regUser2GroupSql);
            $regUser2GroupObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $regUser2GroupObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $regUser2GroupObj->execute();
        } catch (PDOException $e) {
        }
    }

    public function getGroupList()
    {
        $getGroupListSql = "SELECT * FROM userGroupList";

        try {
            $getGroupListObj = $this->dbh->prepare($getGroupListSql);
            $getGroupListObj->execute();
            return $getGroupListObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getGroupUser($groupId)
    {
        $getGroupUserSql = "SELECT user.userName,user.description,userGroupList.groupName
                            FROM `user`
                            RIGHT JOIN userGroup ON user.userId = userGroup.userId
                            LEFT JOIN userGroupList ON userGroupList.groupId = userGroup.groupId
                            WHERE userGroupList.groupId = :groupId;";
        try {
            $getGroupUserObj = $this->dbh->prepare($getGroupUserSql);
            $getGroupUserObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $getGroupUserObj->execute();
            return $getGroupUserObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
