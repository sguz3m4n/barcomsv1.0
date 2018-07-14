<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class Payroll {

    function __construct() {
        
    }

    public $BatchDate;
    public $BatchId;
    public $User;
    public $auditok;

    function CreateBatch() {
        $conn = conn();
        $sql = "INSERT INTO `payrolbatch`(`BatchId`, `BatchDate`, `CreatedBy`, `Created`) VALUES ('$this->BatchId','$this->BatchDate','$this->User',NOW())";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        conn()->close();
    }

    function GenerateTimestamp($schema) {
        $varschema = $schema;
        $vardatestamp = date("ymdGis", time());

        return $varschema . $vardatestamp;
    }

    function CreateAuditRecord($tranid, $trandesc, $username) {
        $conn = conn();
        $sql = "INSERT INTO `transactionaudit` (`TransId`, `TranDesc`, `UserName`, `TranDate`) VALUES ('$tranid', '$trandesc', '$username', NOW()) ";

        if ($conn->exec($sql) === TRUE) {
            echo "New record created successfully";
        }
        conn()->close();
    }

    function ProcessPayments() {
        $conn = conn();
        if ($this->CreateBatch()) {
            $sql = "UPDATE `payment` SET `Status`='Processed',`CloseDate`='$this->BatchDate' WHERE `CloseDate` IS NULL  AND `Status`='Active' ";
            if ($conn->exec($sql)) {
                $this->auditok = 1;
            } else {
                $this->auditok = 0;
            }
        }
        $conn = NULL;
    }

}
