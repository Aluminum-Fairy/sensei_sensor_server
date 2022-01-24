<?php

require_once __DIR__ . "/../config/SQL_Login.php";
require_once __DIR__ . "/Verify.php";

class Tag
{
    use Verify;

    protected PDO $dbh;

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

    public function addTag($userId, $description, $macAddress)
    {
        $addTagSql = "INSERT INTO tag (userId, description, MACAddress) VALUES (:userId,:description,:macAddress)";
        try {
            $addTagObj = $this->dbh->prepare($addTagSql);
            $addTagObj->bindValue(":userId", $userId, PDO::PARAM_INT);
            $addTagObj->bindValue(":description", htmlspecialchars($description), PDO::PARAM_STR);
            $addTagObj->bindValue(":maccAddress", $macAddress, PDO::PARAM_STR);
            $addTagObj->execute();
            return true;
        } catch (PDOException $e) {

        }
        return false;
    }

    public function delTag($tagId)
    {
        if (!$this->tagExist($tagId)) {
            return false;
        }

        $delTagSql = "DELETE FROM tag WHERE tagId = :tagId";
        try {
            $delTagObj = $this->dbh->prepare($delTagSql);
            $delTagObj->bindValue(":tagId", $tagId, PDO::PARAM_INT);
            $delTagObj->execute();
            return true;
        } catch (PDOException $e) {

        }
        return false;
    }

    public function getTagInfo($tagId)
    {
        if (!$this->tagExist($tagId)) {
            return false;
        }

        $getTagInfoSql = "SELECT * FROM tag WHERE tagId = :tagId";
        try{
            $getTagInfoObj = $this->dbh->prepare($getTagInfoSql);
            $getTagInfoObj->bindValue(":tagId",$tagId,PDO::PARAM_INT);
            $getTagInfoObj->execute();
            return $getTagInfoObj->fetchAll(PDO::FETCH_COLUMN);
        }catch (PDOException $e){

        }
        return  false;
    }

    public function setTag($tagInfo)
    {
        if ($this->tagExist($tagInfo["tagId"])) {
            $tagSetSql = "UPDATE tag SET userId = :userId,description = :description,MACAddress = :macAdress ,updateTime = :updateTime WHERE tagId = :tagId";
        } else {
            $tagSetSql = "INSERT INTO tag (tagId, userId, description, MACAddress, updateTime) VALUES (:tagId,:userId,:description,:macAddress,:updateTime)";
        }

        try {
            $tagSetObj = $this->dbh->prepare($tagSetSql);
            $tagSetObj->bindValue(":tagId", $tagInfo["tagId"], PDO::PARAM_INT);
            $tagSetObj->bindValue(":userId", $tagInfo["userId"], PDO::PARAM_INT);
            $tagSetObj->bindValue(":description", htmlspecialchars($tagInfo["description"]), PDO::PARAM_STR);
            $tagSetObj->bindValue(":macAddress", $tagInfo["macAddress"], PDO::PARAM_STR);
            $tagSetObj->bindValue(":updateTime", $tagInfo["updateTime"], PDO::PARAM_STR);
            $tagSetObj->execute();
        } catch (PDOException $e) {

        }
        return false;
    }

    public function checkTagUpdate($tagInfo)
    {
        #タグのアップデート情報
        if (!$this->tagExist($tagInfo["tagId"])) {
            return 0;
        }
        $getTagUpdateSql = "SELECT * FROM tag WHERE tagId = :tagId AND updateTime > :updateTime";
        try {
            $getTagUpdateObj = $this->dbh->prepare($getTagUpdateSql);
            $getTagUpdateObj->bindValue(":tagId", $tagInfo["tagId"], PDO::PARAM_INT);
            $getTagUpdateObj->bindValue("updateTime", $tagInfo["updateTime"], PDO::PARAM_STR);
            $getTagUpdateObj->execute();

            return $getTagUpdateObj->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {

        }
        return false;

    }

    public function getTagIdList()
    {
        $getTagIdListSql = "SELECT tagId FROM tag ";
        try {
            $getTagIdListObj = $this->dbh->prepare($getTagIdListSql);
            $getTagIdListObj->execute();
            return $getTagIdListObj->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {

        }
        return false;
    }

    public function getLastTagUpdateTime()
    {
        $getLastTagUpdateTimeSql = "SELECT tagId,updateTime FROM tag";
        try {
            $getLastTagUpdateTimeObj = $this->dbh->prepare($getLastTagUpdateTimeSql);
            $getLastTagUpdateTimeObj->execute();
            return $getLastTagUpdateTimeObj->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException  $e) {

        }
        return false;
    }
}
