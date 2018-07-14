<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class Audit {

    public $TranId;
    public $TranType;
    public $UserName;
    public $TranDesc;

    //Method to generate unique timestamp value 
    //Key will be prefixed with schema var and follow the format
    //1710211906
    //2 character year-- 2 character month-- 2 character date-- 2 character 24hour time -- 2 character min
    function GenerateTimestamp($schema) {
        $varschema = $schema;
        $vardatestamp = date("ymdGis", time());
        return $varschema . $vardatestamp;
    }

    //Method to create transaction audit record
    //This should be invoked anytime a transactional event is executed in the interface
    function CreateTransAuditRecord($tranid, $trandesc, $username, $trantype, $tranamt, $compid) {
        $conn = conn();
        $sql = "INSERT INTO `transactionaudit` (`TransId`,`TranType`, `TranAmt`, `TranDesc`,`CompanyId`,`UserName`, `TranDate`) VALUES ('$tranid','$trantype','$tranamt', '$trandesc','$compid', '$username', NOW()) ";
        if ($conn->exec($sql)) {
            //echo "New record created successfully";
        } else {
            //echo "New record created successfully";
        }
        $conn = NULL;
    }

    //Method to create user audit record
    //This should be invoked anytime a user action event is executed in the interface
    function CreateUserAuditRecord($tranid, $username, $action) {
        $conn = conn();
        $sql = "INSERT INTO `useractionaudit` (`TransId`, `UserName`, `ActionName`, `ActionDate`) VALUES ('$tranid', '$username', '$action', NOW()) ";
        if ($conn->exec($sql)) {
            //echo "New record created successfully";
        } else {
            //echo "New record created successfully";
        }
        $conn = NULL;
    }
    
    //Method to create transaction audit record
    //This should be invoked anytime a transactional event is executed in the interface
    function UpdateTransAuditRecord($tranid, $trandesc, $username, $trantype, $tranamt, $compid) {
        $conn = conn();
        $sql = "UPDATE `transactionaudit` SET `TranType`='".$trantype."',"
                . " `TranAmt`='".$tranamt."', `TranDesc`='".$trandesc."',"
                . "`CompanyId`='".$compid."',`UserName`='".$username."',"
                . " `TranDate`=NOW() WHERE `TransId`='".$tranid."'";
        if ($conn->exec($sql)) {
            //echo "New record created successfully";
        } else {
            //echo "New record created successfully";
        }
        $conn = NULL;
    }

    //Method to create user audit record
    //This should be invoked anytime a user action event is executed in the interface
    function UpdateUserAuditRecord($tranid, $username, $action) {
        $conn = conn();
        $sql = "UPDATE `useractionaudit` SET `UserName`='$username',"
                . "`ActionName`= '$action', `ActionDate`=NOW() WHERE "
                . "`TransId`='".$tranid."'";
        if ($conn->exec($sql)) {
            //echo "New record created successfully";
        } else {
            //echo "New record created successfully";
        }
        $conn = NULL;
    }

}
