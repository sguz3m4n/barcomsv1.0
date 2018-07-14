<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Company.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

static $compiderr = "";
static $compidwrapper = "";

class CompanyCreateController extends PermissionController {

    function __construct() {
        $this->setRoles(['Manager', 'Administrator', 'Super User']);
    }

    private $CompId = "";
    private $CompIdIsValid = "";
    private $CompanyName = "";
    private $AddressLine1 = "";
    private $AddressLine2 = "";
    private $AddressLine3 = "";
    private $Parish = "";
    private $PostalCode = "";
    private $ContactName = "";
    private $AgentName = "";
    private $AgentAddress = "";
    private $PhoneNumber = "";
    private $FaxNumber = "";
    private $ReferredBy = "";
    private $Notes = "";
    private $ShipName = "";
    private $Email = "";

//Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        foreach ($elements as $value) {
            if ($value == "CompanyId") {
                if (strlen($this->CompId) < 7) {
                    $compiderr = "Company Id is invalid length";
                    $_SESSION['$compidwrapper'] = '<span style="color:red" >' . " * " . $compiderr . '</span>';
                    $this->CompIdIsValid = 0;
                } else {
                    $this->CompIdIsValid = 1;
                    $_SESSION['$compidwrapper'] = NULL;
                }
            }
        }
    }

//Validation Engine will execute any validation on the fields in the interface
//Render page contents depeding on http method call
//If there is a GET call just render the page contens
//If there is a POST call and data entered passes validation then submit to database
//else POSTBACK data with validations errors
    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create'])) {
//variables for data input 
            $conn = conn();
            $compinst = new \BarcomModel\Company();
            $audinst = new \BarcomModel\Audit();
            $this->CompId = $varid = $compinst->CompanyId = $_POST['CompanyId'];
            $this->CompanyName = $compname = $compinst->CompanyName = $_POST['CompanyName'];
            $this->AddressLine1 = $compinst->AddressLine1 = $_POST['AddressLine1'];
            $this->AddressLine2 = $compinst->AddressLine2 = $_POST['AddressLine2'];
            $this->AddressLine3 = $compinst->AddressLine3 = $_POST['AddressLine3'];
            $this->Parish = $compinst->Parish = $_POST['Parish'];
            $this->PostalCode = $compinst->PostalCode = $_POST['PostalCode'];
            $this->ContactName = $compinst->ContactName = $_POST['ContactName'];
            $this->AgentName = $compinst->AgentName = $_POST['AgentName'];
            $this->AgentAddress = $compinst->AgentAddress = $_POST['AgentAddress'];
            $this->PhoneNumber = $compinst->PhoneNumber = $_POST['PhoneNumber'];
            $this->FaxNumber = $compinst->FaxNumber = $_POST['FaxNumber'];
            $this->ReferredBy = $compinst->ReferredBy = $_POST['ReferredBy'];
            $this->Notes = $compinst->Notes = $_POST['Notes'];
            $this->ShipName = $compinst->ShipName = $_POST['ShipName'];
            $this->Email = $compinst->Email = $_POST['Email'];
            $compinst->CompStatus = 'Active';
            $compinst->DelFlg = 'N';

//Send elements to be validated
            $validateme = ["CompanyId"];
            $this->ValidationEngine($validateme);

//if validation succeeds then commit info to database
            if ($this->CompIdIsValid) {
                if ($compinst->IfExists($compinst->CompanyId) === 1) {
                    $compinst->CreateCompany($username);
                }

//if validation succeeds then log audit record to database
                if ($compinst->auditok == 1) {
                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CCMP');
                    $TranDesc = 'Create New Company for ' . $varid . " Name " . $compname;
                    $User = $username;
                    $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                    $token = '<br><br><span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $compname . '</span><br><br><br>' .
                            '<span class="label label-success">Company Id</span> ' . '<span class="label label-info">' . $varid . '</span><br>';
                    $token1 = 'Record Successfully Created';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            } else {
                //if validation fails do postback with values already entered
                $model = new \BarcomModel\Company();
                $parishes = $model->GetParishes();
                $template = new MasterTemplate();
                $template->load("Views/Company/company.html");
                $template->replace("CompanyId", $this->CompId);
                $template->replace("val_CompanyId", $_SESSION['$compidwrapper']);
                $template->replace("CompanyName", $this->CompanyName);
                $template->replace("AddressLine1", $this->AddressLine1);
                $template->replace("AddressLine2", $this->AddressLine2);
                $template->replace("AddressLine3", $this->AddressLine3);
                $template->replace("PostalCode", $this->PostalCode);
                $template->replace("ContactName", $this->ContactName);
                $template->replace("AgentName", $this->AgentName);
                $template->replace("PhoneNumber", $this->PhoneNumber);
                $template->replace("FaxNumber", $this->FaxNumber);
                $template->replace("Email", $this->Email);
                $template->replace("ReferredBy", $this->ReferredBy);
                $template->replace("ShipName", $this->ShipName);
                $template->publish();
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\Company();
            $parishes = $model->GetParishes();
            $activecomp = $model->CountActive();
            $zerobalance = $model->CountZeroBalance();

            $template = new MasterTemplate();
            $template->load("Views/Company/company.html");
            $template->replace("parishes", $parishes);
            $template->replace("CompanyId", "");
            $template->replace("val_CompanyId", "");
            $template->replace("CompanyName", "");
            $template->replace("val_CompanyName", "");
            $template->replace("CompanyName", "");
            $template->replace("AddressLine1", "");
            $template->replace("AddressLine2", "");
            $template->replace("AddressLine3", "");
            $template->replace("PostalCode", "");
            $template->replace("ContactName", "");
            $template->replace("AgentName", "");
            $template->replace("PhoneNumber", "");
            $template->replace("FaxNumber", "");
            $template->replace("Email", "");
            $template->replace("ReferredBy", "");
            $template->replace("ShipName", "");
            $template->replace("actv_comp", $activecomp);
            $template->replace("zerobal_comp", $zerobalance);
            $template->replace("title", " Create New Customs Employee ");
            $template->publish();
        }
    }

}

?>
