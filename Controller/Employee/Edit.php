<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Employee.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

static $nisnoerr = "";
static $nisnowrapper = "";

class EmployeeEditController extends PermissionController {

    function __construct() {
        $this->setRoles(['Human Resource Clerk', 'Manager', 'Administrator', 'Super User']);
    }

    private $NISNo = "";
    private $NISNoIsValid = "";

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        foreach ($elements as $value) {
            if ($value == "NISNo") {
                if (strlen($this->NISNo) < 6) {
                    $nisnoerr = "NIS No is invalid length";
                    $_SESSION['$nisnowrapper'] = '<span style="color:red">' . " * " . $nisnoerr . '</span>';
                    $this->NISNoIsValid = 0;
                } else {
                    $this->NISNoIsValid = 1;
                    $_SESSION['$nisnowrapper'] = NULL;
                }
            }
        }
    }

    //Validation Engine will execute any validation on the fields in the interface
    //
    ////Render page contents depeding on http method call
    //If there is a GET call just render the page contens
    //If there is a POST call and data entered passes validation then submit to database
    //else POSTBACK data 
    //with validations errors
    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-update'])) {
            $empinst = new \BarcomModel\Employee();
            $audinst = new \BarcomModel\Audit();

            //Get Id from browser interface
            $Natregno = $_POST['Natregno'];

            //Check to see if the record already exists
            //If it does execute update
            if ($empinst->IfExists($Natregno) === 0) {
                $this->Natregno = $varid = $empinst->Natregno = $_POST['Natregno'];
                $empinst->Title = $_POST['Title'];
                $empinst->FirstName = $_POST['FirstName'];
                $empinst->Initial = $_POST['Initial'];
                $empinst->LastName = $_POST['LastName'];
                $name = $empinst->FirstName . " " . $empinst->LastName;
                $empinst->AddressLine1 = $_POST['AddressLine1'];
                $empinst->AddressLine2 = $_POST['AddressLine2'];
                $empinst->AddressLine3 = $_POST['AddressLine3'];
                $empinst->Parish = $_POST['Parish'];
                $empinst->PostalCode = $_POST['PostalCode'];
                $empinst->WorkPhone = $_POST['WorkPhone'];
                $empinst->HomePhone = $_POST['HomePhone'];
                $empinst->CellNo = $_POST['CellNo'];
                $empinst->Ext = $_POST['Ext'];
                $empinst->FaxNum = $_POST['FaxNum'];
                $empinst->Email = $_POST['Email'];
                $empinst->RoleName = $_POST['RoleName'];
                $empinst->RateCode = $_POST['RateCode'];
                $this->NISNo = $empinst->NISNo = $_POST['NISNo'];
                $empinst->Notes = $_POST['Notes'];
                $birthDate = $empinst->DateOfBirth = $_POST['DateOfBirth'];
                $empinst->Age = $age = (date('Y') - date('Y', strtotime($birthDate)));
                ($_POST['Gender'] = "Male" ? $empinst->Gender = "M" : $empinst->Gender = "F" );
                //$empinst->Gender = $_POST['Gender'];
                $empinst->RecModifiedBy = $username;

                //Send elements to be validated
                $validateme = ["NISNo"];
                $this->ValidationEngine($validateme);

                //if validation succeeds then commit info to database
                if (($this->NISNoIsValid)) {
                    $empinst->EditEmployee($Natregno);

                    if ($empinst->auditok == 1) {
                        $tranid = $audinst->TranId = $audinst->GenerateTimestamp('UEMP');
                        $TranDesc = 'Update Employee ' . $varid . " Name " . $name;
                        $User = $username;
                        $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                        $token = '<br><br><span class="label label-success">Employee Name</span> ' . '<span class="label label-info"> ' . $name . '</span><br><br><br>' .
                                '<span class="label label-success">Employee Id</span> ' . '<span class="label label-info">' . $Natregno . '</span><br>';
                        $token1 = 'Record Successfully Updated';
                        header("Location:" . "/success?result=$token&header=$token1&args=");
                    }
                } else {
                    //if validation fails do postback to main page with validation error
                    $template = new MasterTemplate();
                    $template->load("Views/Employee/employee_edit.html");
                    $template->replace("title", "Edit Employee Details ");
                    $template->replace("NISNo", $this->NISNo);
                    $template->replace("val_NISNo", $_SESSION['$nisnowrapper']);
                    $template->publish();
                    //if validation fails do postback to main page with validation error
                }
            }
        } else
        if (isset($_GET)) {
            $template = new MasterTemplate();
            $template->load("Views/Employee/employee_edit.html");
            $template->replace("title", "Edit Employee Details ");
            $template->replace("val_NISNo", "");
            $template->publish();
        }
    }

}
