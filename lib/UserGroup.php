<?php

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";
require_once __DIR__ . "/Define.php";


class UserGroup
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

    public function addUserGroup($groupName)
    {
        $addUserGroupSql = "INSERT INTO userGroupList (groupName) VALUES (:groupName)";
        try {
            $addUserGroupObj = $this->dbh->prepare($addUserGroupSql);
            $addUserGroupObj->bindValue(":groupName", htmlspecialchars($groupName), PDO::PARAM_STR);
            $addUserGroupObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function delUserGroup($groupId)
    {
        if (!$this->groupIdExist($groupId) || !$this->groupMemberExist($groupId)) {
            return false;
        }
        $delUserGroupSql = "DELETE FROM userGroupList WHERE groupId = :groupId";
        try {
            $delUserGroupObj = $this->dbh->prepare($delUserGroupSql);
            $delUserGroupObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $delUserGroupObj->execute();
        } catch (PDOException $e) {
        }
    }

    public function delAllUserFromGroup($groupId)
    {
        $delAllUserFromGroupSql = "DELETE FROM userGroup WHERE groupId = :groupId";
        try {
            $delAllUserFromGroupObj = $this->dbh->prepare($delAllUserFromGroupSql);
            $delAllUserFromGroupObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            return $delAllUserFromGroupObj->execute();
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getGroupList()
    {
        $getGroupListSql = "SELECT * FROM userGroupList";

        try {
            $getGroupListObj = $this->dbh->prepare($getGroupListSql);
            $getGroupListObj->execute();
            return $getGroupListObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getUserFromGroupList($userId)
    {
        if (!$this->userExist($userId)) {
            return false;
        }
        $getUserFromGroupListSql = "SELECT userGroup.groupId,userGroupList.groupName
                                    FROM `user`
                                    RIGHT JOIN userGroup ON user.userId = userGroup.userId
                                    LEFT JOIN userGroupList ON userGroupList.groupId = userGroup.groupId
                                    WHERE userGroup.userId = :userId ";
        try {
            $getUserFromGroupListObj = $this->dbh->prepare($getUserFromGroupListSql);
            $getUserFromGroupListObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $getUserFromGroupListObj->execute();
            return $getUserFromGroupListObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
    }

    public function getGroupUser($groupId)
    {
        $getGroupUserSql = "SELECT user.userId,user.userName,user.description
                            FROM `user`
                            RIGHT JOIN userGroup ON user.userId = userGroup.userId
                            WHERE userGroup.groupId = :groupId;";
        try {
            $getGroupUserObj = $this->dbh->prepare($getGroupUserSql);
            $getGroupUserObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $getGroupUserObj->execute();
            return $getGroupUserObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function getGroupName($groupId)
    {
        if (!$this->groupIdExist($groupId)) {
            return false;
        }
        $getGroupNameSql = "SELECT groupName FROM userGroupList WHERE groupId = :groupId";

        try {
            $getGroupNameObj = $this->dbh->prepare($getGroupNameSql);
            $getGroupNameObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $getGroupNameObj->execute();

            return $getGroupNameObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
        }
        return false;
    }

    public function editGroup($groupId, $newGroupName, $userIdArr)
    {
        $delGroupSql = "DELETE FROM userGroup WHERE groupId = :groupId";
        try {
            $delGroupObj = $this->dbh->prepare($delGroupSql);
            $delGroupObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $delGroupObj->execute();
            foreach ($userIdArr as $userId) {
                if (!$this->regUser2Group($userId, $groupId)) {
                    return false;
                }
            }
            return $this->editGroupName($groupId, $newGroupName);
        } catch (PDOException $e) {
        }
        return false;
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
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }

    public function editGroupName($groupId, $newGroupName)
    {
        $editGroupNameSql = "UPDATE userGroupList SET groupName = :groupName WHERE groupId = :groupId";
        try {
            $editGroupNameObj = $this->dbh->prepare($editGroupNameSql);
            $editGroupNameObj->bindValue(":groupName", $newGroupName, PDO::PARAM_STR);
            $editGroupNameObj->bindValue(":groupId", $groupId, PDO::PARAM_INT);
            $editGroupNameObj->execute();
            return true;
        } catch (PDOException $e) {
        }
        return false;
    }
}
