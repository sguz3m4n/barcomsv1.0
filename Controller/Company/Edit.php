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

static $compnameerr = "";
static $compnamewrapper = "";

class CompanyEditController extends PermissionController {

    function __construct() {
        $this->setRoles(['Manager', 'Administrator', 'Super User']);
    }

    private $CompName = "";
    private $CompNameIsValid = "";

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        foreach ($elements as $value) {
            if ($value == "CompanyName") {
                if (strlen($this->CompName) < 3) {
                    $compnameerr = "Company Name is invalid length";
                    $_SESSION['$compnamewrapper'] = '<span style="color:red" >' . " * " . $compnameerr . '</span>';
                    $this->CompNameIsValid = 0;
                } else {
                    $this->CompNameIsValid = 1;
                    $_SESSION['$compnamewrapper'] = NULL;
                }
            }
        }
    }

    //Validation Engine will execute any validation on the fields in the interface
    ////Render page contents depeding on http method call
    //If there is a GET call just render the page contens
    //If there is a POST call and data entered passes validation then submit to database
    //else POSTBACK data 
    //with validations errors
    function show($params) {

        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-update'])) {
            $compinst = new \BarcomModel\Company();
            $audinst = new \BarcomModel\Audit();

            //Get Id from browser interface
            $CompanyId = $_POST['CompanyId'];

            //Check to see if the record already exists            
            //If it does execute update
            if ($compinst->IfExists($CompanyId) === 0) {
                $this->CompId = $varid = $compinst->CompanyId = $_POST['CompanyId'];
                $this->CompName = $compname = $compinst->CompanyName = $_POST['CompanyName'];
                $compinst->AddressLine1 = $_POST['AddressLine1'];
                $compinst->AddressLine2 = $_POST['AddressLine2'];
                $compinst->AddressLine3 = $_POST['AddressLine3'];
                $compinst->Parish = $_POST['Parish'];
                $compinst->PostalCode = $_POST['PostalCode'];
                $compinst->AgentName = $_POST['AgentName'];
                $compinst->AgentAddress = $_POST['AgentAddress'];
                $compinst->ContactName = $_POST['ContactName'];
                $compinst->PhoneNumber = $_POST['PhoneNumber'];
                $compinst->FaxNumber = $_POST['FaxNumber'];
                $compinst->Email = $_POST['Email'];
                $compinst->ReferredBy = $_POST['ReferredBy'];
                $compinst->Notes = $_POST['Notes'];
                $compinst->ShipName = $_POST['ShipName'];
                $compinst->CompStatus = 'Active';
                //$compinst->RecEntered = $_POST['RecEntered'];
                //$compinst->RecEnteredBy = $username;
                //$compinst->RecModified = $_POST['RecModified'];
                $compinst->RecModifiedBy = $username;

                //Send elements to be validated
                $validateme = ["CompanyName"];
                $this->ValidationEngine($validateme);

                //if validation succeeds then commit info to database
                if ($this->CompNameIsValid) {
                    $compinst->EditCompany($CompanyId);

                    if ($compinst->auditok == 1) {
                        $tranid = $audinst->TranId = $audinst->GenerateTimestamp('UCMP');
                        $TranDesc = 'Update Company for ' . $varid . " Name " . $compname;
                        $User = $username;
                        $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                        $token = '<br><br><span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $compname . '</span><br><br><br>' .
                                '<span class="label label-success">Company Id</span> ' . '<span class="label label-info">' . $varid . '</span><br>';
                        $token1 = 'Record Successfully Updated';
                        header("Location:" . "/success?result=$token&header=$token1&args=");
                    }
                } else {
                    //if validation fails do postback to main page with validation error 
                    $template = new MasterTemplate();
                    $template->load("Views/Company/company_edit.html");
                    //$template->replace("parishes", $parishes);
                    $template->replace("title", " Create New Customs Employee ");
                    $template->replace("CompanyName", $this->CompName);
                    $template->replace("val_CompanyName", $_SESSION['$compnamewrapper']);
                    $template->publish();
                }
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\Company();
            $parishes = $model->GetParishes();
            $template = new MasterTemplate();
            $template->load("Views/Company/company_edit.html");
            $template->replace("parishes", $parishes);
            $template->replace("title", " Create New Customs Employee ");
            $template->replace("val_CompanyName", "");
            $template->publish();
        }
    }

}
