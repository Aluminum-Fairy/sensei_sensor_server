<?php

trait Verify
{                                                                                                                        //入力データの検証用各データ処理用クラスファイルよりも先に読み込ませる必要があるため注意
    public function sensorExist($sensorId)
    #センサーがすでに登録されている場合はTrueを返す。
    {
        $checkSql = "SELECT sensorId FROM sensor WHERE sensorId =:sensorId";
        try{
            $checkObj = $this->dbh->prepare($checkSql);
            $checkObj->bindValue(":sensorId",$sensorId);
            $checkObj->execute();
        }catch (PDOException $e){
            http_response_code(500);
            header("Error:".$e);
            exit();
        }
        return $checkObj->fetchColumn() != 0;
    }

    public function userExist($userId){
        $checkSql ="SELECT COUNT(userId) FROM user WHERE userId = :userId";
        try{
            $checkObj = $this->dbh->prepare($checkSql);
            $checkObj->bindValue(":userId",$userId,PDO::PARAM_INT);
            $checkObj->execute();
            return $checkObj->fetchColumin() == 1;
        }catch(PDOException $e){

        }
    }
}
