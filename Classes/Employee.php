<?php

namespace BarcomModel;

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class Employee {

//Employee class properties
    public $Natregno;
    public $LastName;
    public $FirstName;
    public $Initial;
    public $Title;
    public $AddressLine1;
    public $AddressLine2;
    public $AddressLine3;
    public $AddressLine4;
    public $Parish;
    public $PostalCode;
    public $WorkPhone;
    public $HomePhone;
    public $CellNo;
    public $Ext;
    public $RoleName;
    public $PostType;
    public $DateOfBirth;
    public $Age;
    public $Gender;
    public $RateCode;
    public $PayRate;
    public $FaxNum;
    public $Notes;
    public $Email;
    public $NISNo;
    public $EmpStatus;
    public $RecEntered;
    public $RecEnteredBy;
    public $RecModified;
    public $RecModifiedBy;
    public $DelFlg;
    public $auditok;
    public $activepersons;

//CRUD FUNCTIONALITY
    //Create Employee Method
    function CreateEmployee($username) {
        $conn = conn();
        $sql = "INSERT INTO employee(Natregno,LastName,FirstName,Initial,Title,AddressLine1,AddressLine2,AddressLine3,AddressLine4,Parish,PostalCode,WorkPhone,HomePhone,CellNo,Ext,FaxNum,Email,RoleName,RateCode,NISNo,Notes,EmpStatus,RecEntered,RecEnteredBy,RecModified,RecModifiedBy,DelFlg,DateOfBirth,Age,Gender) 
          VALUES('$this->Natregno','$this->LastName','$this->FirstName','$this->Initial','$this->Title','$this->AddressLine1','$this->AddressLine2','$this->AddressLine3','$this->AddressLine4','$this->Parish','$this->PostalCode','$this->WorkPhone','$this->HomePhone','$this->CellNo','$this->Ext',
          '$this->FaxNum','$this->Email','$this->RoleName','$this->RateCode','$this->NISNo','$this->Notes','$this->EmpStatus',NOW(),'$username',NULL,NULL,'$this->DelFlg',NOW(),'$this->Age','$this->Gender')";
        if ($conn->exec($sql)) {
            $this->auditok = 1;
        } else {
            $this->auditok = 0;
        }
        $conn = NULL;
    }

    //Update Employee Method
    function EditEmployee($natregno) {
        $conn = conn();
        $sql = "UPDATE employee SET Title='$this->Title',LastName='$this->LastName',FirstName='$this->FirstName',Initial='$this->Initial',AddressLine1='$this->AddressLine1',AddressLine2='$this->AddressLine2',AddressLine3='$this->AddressLine3', Gender='$this->Gender', Parish='$this->Parish', PostalCode='$this->PostalCode',WorkPhone='$this->WorkPhone',HomePhone='$this->HomePhone',CellNo='$this->CellNo',Ext='$this->Ext',FaxNum='$this->FaxNum',Email='$this->Email', RoleName='$this->RoleName',RateCode='$this->RateCode',NisNo='$this->NISNo',RecModified=NOW(),RecModifiedBy='$this->RecModifiedBy', DateOfBirth='$this->DateOfBirth',Notes='$this->Notes' WHERE Natregno='$natregno' AND DelFlg='N'";
        if ($conn->exec($sql)) {
            $this->auditok = 1;
        } else {
            $this->auditok = 0;
        }
        $conn = NULL;
    }

    //Delete Employee Method
    function DeleteEmployee($natregno) {
        $conn = conn();
        $sql = "UPDATE employee SET DelFlg='Y' WHERE Natregno='$natregno'";
        if ($conn->exec($sql)) {
            $this->auditok = 1;
        } else {
            $this->auditok = 0;
        }
        $conn = NULL;
    }

    //Check to see if Employee currently exist Method
    function IfExists($natregno) {
        $conn = conn();
        $stmt = $conn->prepare("SELECT * FROM employee WHERE Natregno='$natregno';");
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return 1;
        }
        return 0;
        $conn = NULL;
    }

    //Get Role and RateCode from table Method
    //This is to provide values for drop down control on the browser interface
    function GetRoles() {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT rolename,ratecode FROM employeeroles ORDER BY rolename ASC;");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $test = $value['ratecode'];
            $result .= "<option id='$test' >" . $value['rolename'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

    //Get Parishes from table Method
    //This is to provide values for drop down control on the browser interface
    function GetParishes() {
        $result = "";
        $conn = conn();
        $stmt = $conn->prepare("SELECT parish FROM parish ORDER BY parish ASC;");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $result .= "<option>" . $value['parish'] . "</option>";
        }
        return $result;
        $conn = NULL;
    }

    //Get Active Employees Method
    function CountActive() {
        $conn = conn();
        $stmt = $conn->prepare("SELECT count(Natregno) val FROM employee WHERE EmpStatus='Active' AND DelFlg='N';");
        $stmt->execute();
        $result_array = $stmt->fetchAll();
        foreach ($result_array as $value) {
            $this->activepersons = $value['val'];
        }
        return $this->activepersons;
        $conn = NULL;
    }
    
    function GetEmployee($natregno) {
        $sql = " SELECT * FROM `paymentrates` payrate,`employee` emp WHERE emp.ratecode=payrate.ratecode AND Natregno='$natregno'";
        $conn = conn();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }


//View functionality is contained in the shared controls in the Views folder
}
