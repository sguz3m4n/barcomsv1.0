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

static $natregerr = "";
static $natregwrapper = "";
static $nisnoerr = "";
static $nisnowrapper = "";
static $natregunqerr = "";
static $natregunqwrapper = "";

class EmployeeCreateController extends PermissionController {

    function __construct() {
        $this->setRoles(['Human Resource Clerk', 'Manager', 'Administrator', 'Super User']);
    }

    private $Natregno = "";
    private $NatRegIsValid;
    private $NISNoIsValid = "";
    private $NatRegIsUnique = "";
    private $Title = "";
    private $FirstName = "";
    private $Initial = "";
    private $Lastname = "";
    private $AddressLine1 = "";
    private $AddressLine2 = "";
    private $AddressLine3 = "";
    private $Parish = "";
    private $PostalCode = "";
    private $WorkPhone = "";
    private $HomePhone = "";
    private $CellNo = "";
    private $Ext = "";
    private $FaxNum = "";
    private $Email = "";
    private $RoleName = "";
    private $RateCode = "";
    private $NISNo = "";
    private $Notes = "";
    private $Gender = "";

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        $empinst = new \BarcomModel\Employee();
        foreach ($elements as $value) {
            if ($value == "Natregno") {
                if ((strlen($this->Natregno) < 10) || (strlen($this->Natregno) > 10))  {
                    $natregerr = "National Reg No is invalid length";
                    $_SESSION['$natregwrapper'] = '<span style="color:red" >' . " * " . $natregerr . '</span>';
                    $this->NatRegIsValid = 0;
                } else {
                    $this->NatRegIsValid = 1;
                    $_SESSION['$natregwrapper'] = NULL;
                }
            }
            if ($value == "Natregno") {
                if ($empinst->IfExists($this->Natregno) === 0) {
                    $natregunqerr = "This National Registration currently exists";
                    $_SESSION['$natregunqwrapper'] = '<span style="color:red" >' . " * " . $natregunqerr . '</span>';
                    $this->NatRegIsUnique = 0;
                } else {
                    $this->NatRegIsUnique = 1;
                    $_SESSION['$natregunqwrapper'] = NULL;
                }
            }
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

    //Render page contents depeding on http method call
    //If there is a GET call just render the page contens
    //If there is a POST call and data entered passes validation then submit to database
    //else POSTBACK data with validations errors
    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create'])) {
            //variables for data input 
            $empinst = new \BarcomModel\Employee();
            $audinst = new \BarcomModel\Audit();
            //ternary operator example
            (isset($_POST['Natregno']) ? $this->Natregno = $varid = $empinst->Natregno = $_POST['Natregno'] : $this->Natregno = $varid = $empinst->Natregno = "");

            $this->Title = $empinst->Title = $_POST['Title'];
            $this->Lastname = $empinst->LastName = $_POST['LastName'];
            $this->Initial = $empinst->Initial = $_POST['Initial'];
            $this->FirstName = $empinst->FirstName = $_POST['FirstName'];
            $name = $empinst->FirstName . " " . $empinst->LastName;
            $this->AddressLine1 = $empinst->AddressLine1 = $_POST['AddressLine1'];
            $this->AddressLine2 = $empinst->AddressLine2 = $_POST['AddressLine2'];
            $this->AddressLine3 = $empinst->AddressLine3 = $_POST['AddressLine3'];
            $this->Parish = $empinst->Parish = $_POST['Parish'];
            $this->PostalCode = $empinst->PostalCode = $_POST['PostalCode'];
            $this->WorkPhone = $empinst->WorkPhone = $_POST['WorkPhone'];
            $this->HomePhone = $empinst->HomePhone = $_POST['HomePhone'];
            $this->CellNo = $empinst->CellNo = $_POST['CellNo'];
            $this->Ext = $empinst->Ext = $_POST['Ext'];
            $this->FaxNum = $empinst->FaxNum = $_POST['FaxNum'];
            $this->Email = $empinst->Email = $_POST['Email'];
            $this->RoleName = $empinst->RoleName = $_POST['RoleName'];
            $this->RateCode = $empinst->RateCode = $_POST['RateCode'];
            $this->NISNo = $empinst->NISNo = $_POST['NISNo'];
            $this->Notes = $empinst->Notes = $_POST['Notes'];
            $empinst->EmpStatus = 'Active';
            $empinst->DelFlg = 'N';
            //$birthDate = $empinst->DateOfBirth = $_POST['DOB'];
            ($_POST['Gender'] = "Male" ? $this->Gender = $empinst->Gender = "M" : $this->Gender = $empinst->Gender = "F" );
            //$this->Gender = $empinst->Gender = $_POST['Gender'];
            $birthDate = $empinst->DateOfBirth = $_POST['DateOfBirth'];
            $empinst->Age = $age = (date('Y') - date('Y', strtotime($birthDate)));
            $empinst->Age = $age;

            //Send elements to be validated
            $validateme = ["Natregno", "NISNo"];
            $this->ValidationEngine($validateme);

            //if validation succeeds then commit info to database
            if (($this->NatRegIsValid) && ($this->NISNoIsValid) && ($this->NatRegIsUnique)) {
                //if ($empinst->IfExists($empinst->Natregno) === 1) {
                $empinst->CreateEmployee($username);
                //}
                //if validation succeeds then log audit record to database
                if ($empinst->auditok == 1) {
                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CEMP');
                    $TranDesc = 'Create New Employee for ' . $varid . " Name " . $name;
                    $User = $username;
                    $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                    $token = '<br><br><span class="label label-success">Employee Name</span> ' . '<span class="label label-info"> ' . $name . '</span><br><br><br>' .
                            '<span class="label label-success">Employee Id</span> ' . '<span class="label label-info">' . $varid . '</span><br>';
                    $token1 = 'Record Successfully Created';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            } else {
                //if validation fails do postback with values already entered 
                $model = new \BarcomModel\Employee();
                $roles = $model->GetRoles();
                $parishes = $model->GetParishes();
                $template = new MasterTemplate();
                $template->load("Views/Employee/employee.html");
                $template->replace("roles", $roles);
                $template->replace("parishes", $parishes);
                $template->replace("username", $username);
                $template->replace("title", " Create New Customs Employee");
                $template->replace("Natregno", $this->Natregno);
                $template->replace("val_Natregno", $_SESSION['$natregwrapper']);
                $template->replace("NISNo", $this->NISNo);
                $template->replace("val_NISNo", $_SESSION['$nisnowrapper']);
                $template->replace("val_Unique", $_SESSION['$natregunqwrapper']);
                $template->replace("Title", $this->Title);
                $template->replace("FirstName", $this->FirstName);
                $template->replace("Initial", $this->Initial);
                $template->replace("LastName", $this->Lastname);
                $template->replace("AddressLine1", $this->AddressLine1);
                $template->replace("AddressLine2", $this->AddressLine2);
                $template->replace("AddressLine3", $this->AddressLine3);
                $template->replace("Parish", $this->Parish);
                $template->replace("PostalCode", $this->PostalCode);
                $template->replace("WorkPhone", $this->WorkPhone);
                $template->replace("Ext", $this->Ext);
                $template->replace("FaxNum", $this->FaxNum);
                $template->replace("HomePhone", $this->HomePhone);
                $template->replace("CellNo", $this->CellNo);
                $template->replace("Email", $this->Email);
                $template->publish();
                //if validation fails do postback with values already entered 
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\Employee();
            $roles = $model->GetRoles();
            $parishes = $model->GetParishes();
            $actives = $model->CountActive();
            $template = new MasterTemplate();
            $template->load("Views/Employee/employee.html");
            $template->replace("roles", $roles);
            $template->replace("parishes", $parishes);
            $template->replace("username", $username);
            $template->replace("activeemployees", $actives);
            $template->replace("title", " Create New Customs Employee");
            $template->replace("Natregno", "");
            $template->replace("DOB", "");
            $template->replace("NISNo", "");
            $template->replace("Title", "");
            $template->replace("FirstName", "");
            $template->replace("Initial", "");
            $template->replace("LastName", "");
            $template->replace("AddressLine1", "");
            $template->replace("AddressLine2", "");
            $template->replace("AddressLine3", "");
            $template->replace("PostalCode", "");
            $template->replace("WorkPhone", "");
            $template->replace("Ext", "");
            $template->replace("FaxNum", "");
            $template->replace("HomePhone", "");
            $template->replace("CellNo", "");
            $template->replace("Email", "");
            $template->replace("val_Natregno", "");
            $template->replace("val_DOB", "");
            $template->replace("val_NISNo", "");
            $template->replace("val_Title", "");
            $template->replace("val_FirstName", "");
            $template->replace("val_Initial", "");
            $template->replace("val_LastName", "");
            $template->replace("val_AddressLine1", "");
            $template->replace("val_AddressLine2", "");
            $template->replace("val_AddressLine3", "");
            $template->replace("val_PostalCode", "");
            $template->replace("val_WorkPhone", "");
            $template->replace("val_Ext", "");
            $template->replace("val_FaxNum", "");
            $template->replace("val_HomePhone", "");
            $template->replace("val_CellNo", "");
            $template->replace("val_Email", "");
            $template->replace("val_Unique", "");
            $template->publish();
        }
    }

}
?>



