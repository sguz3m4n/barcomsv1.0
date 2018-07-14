<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */


require 'Deposit.php';

class Refund extends Deposit {

    public function __construct() {
        ;
    }

    function HasAdequateBalance($companyid, $refundamt) {
        $conn = conn();
        $sql = "SELECT CurrentBalance FROM `deposit` WHERE CurrentBalance>0 AND CurrentBalance>='$refundamt' AND CompanyId='$companyid'";
        $stmt = $conn->prepare($sql);
        $status = $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

    function CreateAuditRecord($tranid, $trandesc, $username) {
        $sql = "INSERT INTO `transactionaudit` (`TransId`, `TranDesc`, `UserName`, `TranDate`) VALUES ('$tranid', '$trandesc', '$username', NOW()) ";
        $conn = conn();
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        }
        conn()->close();
    }

    function GenerateTimestamp() {
        $varschema = 'DPST';
        $vardatestamp = date("ymdGis");
        $vartimestamp = 'world';
        return $varschema . $vardatestamp;
    }

}
