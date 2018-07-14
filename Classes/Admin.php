<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class UserModule {

    public $Natregno;
    public $UserName;
    public $Password;
    public $FirstName;
    public $Roles;
    public $LastLogin;
    public $SystemRoles = array('Human Resource Clerk', 'Manager', 'Administrator', 'Super User', 'Receipt Clerk', 'Payment Clerk', 'Disable');

    //Create New User
    function CreateNewUser() {
        $conn = conn();
        $this->Password = $this->Hasher($this->Password);
        $sql = "INSERT INTO `users`(`national_id`, `username`, `password`, `firstname`, `roles`, `lastlogon`) VALUES  ('$this->Natregno','$this->UserName','$this->Password','$this->FirstName','$this->Roles',NULL)";

        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Method to hash password
    private function Hasher($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    //Edit Existing User
    function UpdateExistingUser($Natid) {
        $conn = conn();
        $this->Password = $this->Hasher($this->Password);

        $sql = "UPDATE `users` SET `national_id`='$this->Natregno',`username`='$this->UserName',`password`='$this->Password',`firstname`='$this->FirstName',`roles`='$this->Roles' WHERE national_id='$Natid'";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Check to see if user currently exists
    function IfExists($natregno) {
        $conn = conn();
        $sql = "SELECT * FROM users WHERE national_id='$natregno'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

}

class ManageTravel {

    public $TravCode;
    public $TravRate;
    public $Modified;
    public $ModifiedBy;

    //Create New Travel Rate
    function CreateTravelRate() {
        $conn = conn();
        $sql = "INSERT INTO `travelrates`(`TravelCode`, `TravelAmount`, `Modified`, `ModifiedBy`) 
        VALUES ('$this->TravCode','$this->TravRate',NOW(),'$this->ModifiedBy')";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Get Travel Codes
    function GetTravelCodes() {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT `TravelCode` FROM `travelrates`;");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $test = $value['TravelCode'];
            $result .= "<option id='$test' >" . $value['TravelCode'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

    //Get Travel Codes
    function GetTravelRate($travelcode) {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT `TravelAmount` FROM `travelrates` WHERE `TravelCode`='$travelcode';");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $travelamt = $value['TravelAmount'];
        }
        return $travelamt;
        $conn = NULL;
    }

    //Edit Travel Rate
    function UpdateTravelRate($travcode) {
        $conn = conn();
        $sql = "UPDATE `travelrates` 
        SET `TravelCode`='$this->TravCode',`TravelAmount`='$this->TravRate',`Modified`=NOW(),`ModifiedBy`='$this->ModifiedBy' WHERE `TravelCode`='$travcode'";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Check to see if user currently exists
    function IfExists($travelcode) {
        $conn = conn();
        $sql = "SELECT * FROM `travelrates` WHERE TravelCode='$travelcode'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

}

class ManagePOT {

    public $RoleCode;
    public $RoleName;
    public $RateCode;
    public $RateAmount;
    public $DateModified;
    public $ModifiedBy;

    //Create New Overtime Payment Rate
    function CreateNewPOT() {
        $conn = conn();
        $sqlprnt = "INSERT INTO `employeeroles`(`RoleCode`, `RoleName`, `RateCode`, `DateModified`, `MofidiedBy`) VALUES "
                . "('$this->RoleCode','$this->RoleName','$this->RateCode',NOW(),'$this->ModifiedBy')";
        $sql = "INSERT INTO `paymentrates`(`RateCode`, `RateAmount`, `DateModified`, `ModifiedBy`) 
        VALUES ('$this->RateCode','$this->RateAmount',NOW(),'$this->ModifiedBy')";
        if (($conn->exec($sqlprnt)) && ($conn->exec($sql))) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Edit Overtime Payment Rate
    function UpdateOT($RateCode) {
        $conn = conn();
        $sql = "UPDATE `paymentrates` SET `RateCode`='$this->RateCode',`RateAmount`='$this->RateAmount',
        `DateModified`=NOW(),`ModifiedBy`='$this->ModifiedBy' WHERE `RateCode`='$RateCode'";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Edit Overtime Payment Rate
    function GetHoliday($HolidCode) {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM `specialdates` WHERE `HolidayCode`='$HolidCode'");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        return $result_array;
        $conn = NULL;
    }

    //Check to see if rate currently exists
    function IfExists($ratecode) {
        $conn = conn();
        $sql = "SELECT * FROM `paymentrates` WHERE RateCode='$ratecode'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

    //Get Travel Codes
    function GetRateCode() {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT `RateCode` FROM `paymentrates`;");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $test = $value['RateCode'];
            $result .= "<option id='$test' >" . $value['RateCode'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

}

class ManageOther {

    public $HolidCode;
    public $HolidDesc;
    public $HolidDate;
    public $CalYear;
    public $Modified;
    public $ModifiedBy;
    public $RateCode;
    public $SpecialRate;

    //Create New Holiday/Weekend Rate
    function CreateManageOther() {
        $conn = conn();
        $sql = "INSERT INTO `specialdates`(`HolidayCode`, `HolidayDesc`, `HolidayDate`, `CalYear`, `Modified`, `ModifiedBy`) VALUES ('$this->HolidCode','$this->HolidDesc','$this->HolidDate','$this->CalYear',NOW(),'$this->ModifiedBy')";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Create New Holiday/Weekend Rate
    function CreateSpecialRate($ratecode, $holidaycode) {
        $conn = conn();
        $sql = "INSERT INTO `specialrates`(`RateCode`, `HolidayCode`, `SpecialRate`, `Modified`, `ModifiedBy`) "
                . "VALUES ('$ratecode','$holidaycode','$this->SpecialRate',NOW(),'$this->ModifiedBy')";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    //Edit Holiday/Weekend Rate
    function UpdateManageOther($HoildCode, $RateCode) {
        $conn = conn();
        $sql = "UPDATE `specialrates` SET `RateCode`='$RateCode',`HolidayCode`='$HoildCode',`SpecialRate`='$this->SpecialRate',`Modified`=NOW(),`ModifiedBy`='$this->ModifiedBy' WHERE `RateCode`='$RateCode' AND `HolidayCode`='$HoildCode'";
        if ($conn->exec($sql)) {
            return 1;
        } else {
            return 0;
        }
        $conn = NULL;
    }

    function GetRates() {
        $GetRates = new ManagePOT();
        return $GetRates->GetRateCode();
    }

    function GetHolidayCode() {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM `specialdates`;");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $test = $value['HolidayCode'];
            $result .= "<option id='$test' >" . $value['HolidayCode'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

    //Check to see if rate currently exists
    function IfExists($holidaycode) {
        $conn = conn();
        $sql = "SELECT * FROM `specialdates` WHERE RateCode='$holidaycode'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

    //Check to see if rate currently exists
    function IfSpecialExists($ratecode, $holidaycode) {
        $conn = conn();
        $sql = "SELECT * FROM `specialrates` WHERE `RateCode`='$ratecode' AND `HolidayCode`='$holidaycode'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

}
