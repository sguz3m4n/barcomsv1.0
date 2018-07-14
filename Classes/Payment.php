<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class Payment {

    function __construct() {
        
    }

    public $TranId;
    public $TranType;
    public $BllFormNo;
    public $BillRefNo;
    public $TransNumber;
    public $PRN;
    public $RecEntered;
    public $OfficerId;
    public $OfficerName;
    public $Post;
    public $PayRate;
    public $RateCode;
    public $CompanyId;
    public $CompanyName;
    public $CompanyAddress;
    public $AgentName;
    public $ContactName;
    public $PhoneNumber;
    public $FaxNumber;
    public $Email;
    public $CurrentBalance;
    public $PreviousBalance;
    public $CompanyLocation;
    public $CompanyCoordinates;
    public $OfficerLocation;
    public $OfficerCoordinates;
    public $Distance;
    public $TravelAmount;
    public $OvertimeAmount;
    public $SubsistenceAmount;
    public $RefundAmount;
    public $HolidayAmount;
    public $OtherAmount;
    public $TotalAmount;
    public $Passengers;
    public $NumberPx;
    public $HoursWorked;
    public $Subsistence;
    public $CompanyBalance;
    public $InspectionDate;
    public $StartDate;
    public $EndDate;
    public $DateIssued;
    public $Comments;
    public $DatePayment;
    public $NISDeductions;
    public $PAYEDeductions;
    public $RecEnteredBy;
    public $auditok;

    function PayMe() {
        
    }

    function GetFilteredPRN($compid) {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT prnmanage.PRNumber,prntran.RequestorID FROM `prnmanage` as prnmanage, prntransaction as prntran WHERE prnmanage.TranId=prntran.TranId and prnmanage.status='Active' and prntran.CompanyId='$compid';");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        $result = "<option id=' ' >" . " " . "</option>";
        foreach ($result_array as $value) {
            $PRNNo = $value['PRNumber'];
            $result .= "<option id='$PRNNo' >" . $value['PRNumber'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

    //Method to calcuate payment based on distance travelled
    function DistanceEarned($distance, $haspassenger) {
        $inst = new Travel();

        if ($haspassenger === TRUE) {
            $rate = $inst->GetTravelRate('01');
        } else {
            $rate = $inst->GetTravelRate('00');
        }
        $earnings = $rate * $distance;
        $earnings = number_format($earnings, 2, '.', '');
        return $earnings;
    }

    //Method to calcuate payment based on overtime hours worked
    function OTEarned($hours, $rate) {
        $earnings = $rate * $hours;
        $earnings = number_format($earnings, 2, '.', '');
        return $earnings;
    }

    //Method to calcuate payment based on if subsiatence is claimed
    function SubsistenceEarned($hassubs) {
        $inst = new Subsistence();
        if ($hassubs === TRUE) {
            $subs = $inst->GetSubsistenceRate();
        } else {
            $subs = 0;
        }
        return $subs;
    }

    //Method to create payment record in database
    function CreatePaymentTransaction($TranId, $CompanyId, $BillFormNo, $BillRef, $InspectionDate, $StartDate, $EndDate) {

        $conn = conn();
        $sql = "INSERT INTO `payment` (`TransId`, `CompanyId`, `Natregno`, `RateCode`, `Distance`, `Passengers`, `HoursWorked`, `Subsistence`, `BillRefNo`, `RN`,
         `TranType`, `TravelAmount`, `OvertimeAmount`, `SubsistenceAmount`, `RefundAmount`, `HolidayAmount`, `OtherAmount`, 
        `TotalPaymentAmount`, `InspectionDate`, `StartDate`, `EndDate`, `RecEntered`, `RecEnteredBy`, `CloseDate`, `PaymentDate`, `NISDeduction`, `PAYEDeduction`, `OvertimeRate`, `PassengerNum`,`Status`) VALUES 
        ('$TranId', '$CompanyId', '$this->OfficerId', '$this->RateCode', '$this->Distance', '$this->Passengers', '$this->HoursWorked', '$this->Subsistence', 
        '$BillFormNo','$BillRef', '$this->TranType', '$this->TravelAmount', '$this->OvertimeAmount', '$this->SubsistenceAmount', '$this->RefundAmount', '$this->HolidayAmount', 
        '$this->OtherAmount', '$this->TotalAmount', '$InspectionDate', '$StartDate','$EndDate', NOW(),
        '$this->RecEnteredBy',NULL, NOW(),$this->NISDeductions,$this->PAYEDeductions,$this->PayRate,$this->NumberPx,'Active') ";

            if ($conn->exec($sql)) {
                $this->auditok = 1;
            } else {
                $this->auditok = 0;
            }
        $conn = NULL;
    }

    function UpdatePaymentTransaction($tranid) {
        $conn = conn();
        $sql = "UPDATE `payment` SET `CompanyId`=" . $this->CompanyId . ",`Natregno`="
                . $this->OfficerId . ",`Distance`=" . $this->Distance . ", `Passengers`='"
                . $this->Passengers . "', `HoursWorked`=" . $this->HoursWorked
                . ", `Subsistence`='" . $this->Subsistence . "',`TravelAmount`="
                . $this->TravelAmount . ", `OvertimeAmount`=" . $this->OvertimeAmount
                . ", `SubsistenceAmount`=" . $this->SubsistenceAmount . ",`TotalPaymentAmount`="
                . $this->TotalAmount . ",`PassengerNum`=" . $this->NumberPx
                . " WHERE TransId ='" . $tranid . "'";
        if ($conn->exec($sql)) {
            $this->auditok = 1;
        } else {
            $this->auditok = 0;
        }
        $conn = NULL;
    }

    function UnlockPayment($rn) {
        $conn = conn();
        $sql = "UPDATE `payment` SET `CloseDate`=NULL"
                . " WHERE TransId ='" . $rn . "'";
        if ($conn->exec($sql)) {
            return true;
        } else {
            return false;
        }
        $conn = NULL;
    }

    function GetPayRate($transid) {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT OvertimeRate FROM `payment` "
                . "WHERE TransId='$transid' AND `Status`='Active';");
        $stmt->execute();
        $result = $stmt->fetch();
        if (isset($result['OvertimeRate'])) {
            return $result['OvertimeRate'];
        } else {
            return NULL;
        }
        $conn = NULL;
    }

    function GetCompanyBalance($compid) {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT `CurrentBalance` FROM `deposit` WHERE `CompanyId`='$compid'");
        $stmt->execute();
        $result = $stmt->fetch();
        if (isset($result['CurrentBalance'])) {
            return $result['CurrentBalance'];
        } else {
            return NULL;
        }
        $conn = NULL;
    }

    //Method to generate unique timestamp value 
    //Key will be prefixed with schema var and follow the format
    //1710211906
    //2 character year-- 2 character month-- 2 character date-- 2 character 24hour time -- 2 character min
    function GenerateTimestamp($schema) {
        $varschema = $schema;
        $vardatestamp = date("ymdGis", time());

        return $varschema . $vardatestamp;
    }

    function PRNChecker($prnnumber) {
        $conn = conn();
        $sql = "SELECT * FROM `prnmanage` WHERE `PRNumber`='$prnnumber'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

    //Method to reclaim PRN that was previously disbursed
    //This should execute when the payment is made to the officer
    function PRNSetter($prnnumber, $username) {
        $conn = conn();
        $sql = "UPDATE `prnmanage` SET `Status`='Reclaimed',
    `ModifiedBy`='$username',`ModifiedDate`=NOW() WHERE `PRNumber`='$prnnumber' ";
        if ($conn->exec($sql)) {
            //$this->auditok = 1;
        } else {
            //$this->auditok = 0;
        }
        $conn = NULL;
    }

    function GetFilteredPayments(array $args) {
        $conn = conn();
        $sql = 'SELECT * FROM payment as A JOIN company as B ON A.CompanyId = B.CompanyId '
                . 'JOIN employee as C ON A.natregno = C.natregno WHERE ';
        $sql = $sql . implode(' ', $args);

        if ($stmt = $conn->prepare($sql)) {
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $result = $stmt->fetchAll();
                // Check number of rows in the result set
                if (!empty($result)) {
                    return $result;
                }
            } else {
                return array();
            }
        }
    }
    // function GetFilteredPayments(array $args) {
    //     $conn = conn();
    //     $sql = 'SELECT * FROM payment as A JOIN company as B ON A.CompanyId = B.CompanyId '
    //             . 'JOIN employee as C ON A.natregno = C.natregno WHERE ';
    //     $sql = $sql . implode(' ', $args);
 
    //     if ($stmt = $conn->prepare($sql)) {
    //         // Attempt to execute the prepared statement
    //         if ($stmt->execute()) {
    //             $result = $stmt->fetchAll();
    //             return $result;
    //         }   
    //     }
    //     return NULL;      
    // }

}

class PayScale {

    function __construct() {
        
    }

    protected $RateAmount;
    protected $RateCode;
    public static $PayRate;

    //Method to test if payment is weekend date
    //Weekend dates need to have different payrates
    function IsWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    function IsHoliday($date) {
        $conn = conn();
        $sql = "SELECT * FROM specialdates dates WHERE dates.HolidayDate='$date';";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            foreach ($result as $value) {
                //$hold = date_format($value['HolidayDate'], 'y-m-d');
                $hold = substr($value['HolidayDate'], 0, -9);
                if ($hold == $date) {
                    return 1;
                }
            }
        }
        return 0;
    }

    function SetWeekEnd($empid) {
        $conn = conn();
        $sql = "SELECT rates.SpecialRate,rates.RateCode FROM specialrates rates, employee employee WHERE  
            employee.RateCode=rates.RateCode AND rates.HolidayCode='WKND' AND employee.Natregno='$empid'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (!empty($result)) {
            foreach ($result as $value) {
                //$this->RateAmount = $value['SpecialRate'];
                //$this->RateCode = $value['RateCode'];
                PayScale::$PayRate = $value['SpecialRate'];
            }
        }
        $conn = NULL;
    }

    public function SetRegular($empid) {
        $conn = conn();
        $sql = "SELECT rates.RateCode,rates.RateAmount FROM paymentrates rates , employee employee WHERE 
            employee.RateCode=rates.RateCode AND employee.Natregno='$empid' AND employee.DelFlg='N'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $PayRate = 0;
        if (!empty($result)) {
            foreach ($result as $value) {
                //$this->RateCode = $value['RateCode'];
                //$this->PayRate = $value['RateAmount'];
                PayScale::$PayRate = $value['RateAmount'];
            }
        }
        $conn = NULL;
    }

    //Method to check the set holiday date and apply relevant rates
    function SetHoliday($StartDate) {
        $conn = conn();
        //$sql = "SELECT `HolidayCode`,`HolidayDesc` FROM `specialdates` WHERE `HolidayDate`='$StartDate'";
        $sql = "SELECT RateCode,dates.HolidayCode,SpecialRate FROM specialrates rates, specialdates dates WHERE rates.HolidayCode=dates.HolidayCode AND dates.HolidayDate='$StartDate'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            foreach ($result as $value) {
                //$this->RateAmount = $value['SpecialRate'];
                //$this->RateCode = $value['RateCode'];
                PayScale::$PayRate = $value['SpecialRate'];
            }
        }
        $conn = NULL;
        //Check to see if working period is a range
    }

    //Check to see if working period is a range or a single working day
    function IsRange($StartDate, $EndDate) {
        if ($StartDate == $EndDate) {
            return 0;
        }
        return 1;
    }

    function GetPayRate($empid, $date, $type) {
        //Check to see if working period is a range or a single working day
        //Calcualte Weekend rates
        if ($type == "WKND") {
            $this->SetWeekEnd($empid);
        } else
        //Calculate Regular rates 
        if ($type == "RGLR") {
            $this->SetRegular($empid);
        } else
        if ($type == "HLDY") {
            $this->SetHoliday($date);
        }
    }

}

class Travel {

    protected $TravelRate;
    public $TravelCode;

    function GetTravelRate($trvlcd) {
        $conn = conn();
        $sqlrate = "SELECT travelamount FROM travelrates WHERE travelcode='$trvlcd';";
        $stmt = $conn->prepare($sqlrate);
        $stmt->execute();
        $result = $stmt->fetchAll();
        //$result = $conn->query($sqlrate);
        if (!empty($result)) {
            //while ($row = $result->fetch_assoc())
            foreach ($result as $value) {
                $this->TravelRate = $value['travelamount'];
            }
        }
        $conn = NULL;
        //conn()->close();
        return $this->TravelRate;
    }

}

class Subsistence {

    protected $Subsistence = 25;

    function GetSubsistenceRate() {
        return number_format($this->Subsistence, 2);
    }

}
