<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Deposit.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

static $asycuderr = "";
static $asycudawrapper = "";
static $depamounterr = "";
static $depamountwrapper = "";
static $compiderr = "";
static $compidwrapper = "";

class MakeDepositController extends PermissionController {

    function __construct() {
        $this->setRoles(['Receipt Clerk', 'Manager', 'Super User']);
    }

    private $Asycuda = "";
    private $AsycudaIsValid = "";
    private $DepAmount = "";
    private $DepAmountIsValid = "";
    private $CompId = "";
    private $CompIdIsValid = "";

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        foreach ($elements as $value) {
            if ($value == "ASYCUDANum") {
                $Rtest = substr($this->Asycuda, 0, 1);
                if (($Rtest != 'R')) {
                    $asycuderr = "ASYCUDA number is invalid format";
                    $_SESSION['$asycudawrapper'] = '<span style="color:red" >' . " * " . $asycuderr . '</span>';
                    $this->AsycudaIsValid = 0;
                } else {
                    $this->AsycudaIsValid = 1;
                    $_SESSION['$asycudawrapper'] = NULL;
                }
            }
            if ($value == "Deposit") {
                if ($this->DepAmount <= 0) {
                    $depamounterr = "Deposit amount must be greater than $0.00";
                    $_SESSION['$depamountwrapper'] = '<span style="color:red" >' . " * " . $depamounterr . '</span>';
                    $this->DepAmountIsValid = 0;
                } else {
                    $this->DepAmountIsValid = 1;
                    $_SESSION['$depamountwrapper'] = NULL;
                }
            }
            if ($value == "CompId") {
                if (($this->CompId == "") || ($this->CompId == NULL)) {
                    $compiderr = "You must select a company";
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
    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create'])) {
            //variables for data input 
            $depinst = new \BarcomModel\Deposit();
            $audinst = new \BarcomModel\Audit();
            //ternary operator to see if post variable has been set or not
            (isset($_POST['CompId']) ? $this->CompId = $varid = $depinst->CompanyId = $_POST['CompId'] : $this->CompId = $varid = $depinst->CompanyId = "");
            if ($varid != "") {
                //If the company is making deposit for the first time
                if ($depinst->IfExists($varid) === 0) {
                    $asycuda = $this->Asycuda = $depinst->ASYCUDA = $_POST["ASYCUDANum"];
                    $CompanyName = $depinst->CompanyName = $_POST["CompName"];
                    $depinst->Comments = $comments = 'New Deposit Account';
                    $depamount = number_format($_POST["Deposit"], 2, '.', '');
                    $prevamount = number_format(0, 2, '.', '');
                    $curamount = $depinst->CurrentBalance = $depinst->DepositAmount = $this->DepAmount = $depamount;
                    $depinst->PreviousBalance = $prevamount;
                    $depinst->ReEnteredBy = $username;

                    //Send html post elements to be validated
                    $validateme = ["ASYCUDANum", "Deposit", "CompId"];
                    $this->ValidationEngine($validateme);

                    //if validation succeeds then commit info to database
                    if (($this->AsycudaIsValid) && ($this->DepAmountIsValid) && ($this->CompIdIsValid)) {
                        //commit record to deposit table
                        $depinst->CreateDeposit();
                        if ($depinst->auditok == 1) {
                            //generate unique timestamp
                            $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CMPD');
                            $AudtDesc = 'Create Deposit for ' . $varid . ' ASYCUDA ' . $this->Asycuda;
                            //$TranDesc = 'Deposit to ' . $varid . " Amt " . $depamount;
                            $TranDesc = $comments;
                            $User = $username;
                            //commit record to transaction audit table and user transaction audit table
                            $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                            $audinst->CreateTransAuditRecord($tranid, $TranDesc, $username, "DEP", $depamount, $varid);
                            $audinst->CreateDepositTransactionRecord($tranid, $asycuda, $TranDesc, $username, "DEP", $depamount, $varid);

                            $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $varid . '</span><br><br><br>' .
                                    '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $CompanyName . '</span><br><br><br>' .
                                    '<span class="label label-success">Previous Balance</span> ' . '<span class="label label-info"> ' . '$' . number_format($prevamount, 2, '.', ',') . '</span><br><br><br>' .
                                    '<span class="label label-success">Deposit Amount</span> ' . '<span class="label label-info">' . '$' . number_format($depamount, 2, '.', ',') . '</span><br>';
                            $token1 = 'Record Successfully Created';
                            header("Location:" . "/success?result=$token&header=$token1&args=");
                        }
                    } else {
                        //if validation fails do postback with values already entered 
                        $template = new MasterTemplate();
                        $template->load("Views/Deposit/deposit.html");
                        $template->replace("title", " Create New Company Deposit");
                        $template->replace("val_AsycudaNum", $_SESSION['$asycudawrapper']);
                        $template->replace("ASYCUDANum", $this->Asycuda);
                        $template->replace("val_Deposit", $_SESSION['$depamountwrapper']);
                        $template->replace("Deposit", $this->DepAmount);
                        $template->replace("val_CompId", $_SESSION['$compidwrapper']);
                        $template->replace("CompId", $this->CompId);
                        $template->publish();
                    }
                } else if ($depinst->IfExists($varid) == 1) {
                    $asycuda = $this->Asycuda = $depinst->ASYCUDA = $_POST["ASYCUDANum"];
                    $CompanyName = $depinst->CompanyName = $_POST["CompName"];
                    $depinst->Comments = $comments = $_POST["Comments"];
                    $depamount = number_format($_POST["Deposit"], 2, '.', '');
                    $prevamount = number_format($_POST["CompanyBalance"], 2, '.', '');
                    $this->DepAmount = $depinst->DepositAmount = $depamount;
                    $depinst->PreviousBalance = $prevamount;
                    $curamount = $depinst->CurrentBalance = number_format($depinst->CurrentBalance($depamount, $prevamount), 2, '.', '');
                    $depinst->ReEnteredBy = $username;

                    //Send elements to be validated
                    $validateme = ["ASYCUDANum", "Deposit", "CompId"];
                    $this->ValidationEngine($validateme);

                    //if validation succeeds then commit info to database
                    if (($this->AsycudaIsValid) && ($this->DepAmountIsValid) && ($this->CompIdIsValid)) {
                        $depinst->EditDeposit($varid);
                        if ($depinst->auditok == 1) {
                            $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CMPD');
                            $AudtDesc = 'Deposit for ' . $varid . ' ASYCUDA ' . $this->Asycuda;
                            $TranDesc = $comments;
                            $User = $username;
                            $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                            $audinst->CreateTransAuditRecord($tranid, $AudtDesc, $User, "DEP", $depamount, $varid);
                            $audinst->CreateDepositTransactionRecord($tranid, $asycuda, $TranDesc, $username, "DEP", $depamount, $varid);

                            $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $varid . '</span><br><br><br>' .
                                    '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $CompanyName . '</span><br><br><br>' .
                                    '<span class="label label-success">Previous Balance</span> ' . '<span class="label label-info"> ' . '$' . number_format($prevamount, 2, '.', ',') . '</span><br><br><br>' .
                                    '<span class="label label-success">Current Balance</span> ' . '<span class="label label-info"> ' . '$' . number_format($curamount, 2, '.', ',') . '</span><br><br><br>' .
                                    '<span class="label label-success">Deposit Amount</span> ' . '<span class="label label-info">' . '$' . number_format($depamount, 2, '.', ',') . '</span><br>';
                            $token1 = 'Record Successfully Created';
                            header("Location:" . "/success?result=$token&header=$token1&args=");
                        }
                    } else {
                        //if validation fails do postback with values already entered 
                        $template = new MasterTemplate();
                        $template->load("Views/Deposit/deposit.html");
                        $template->replace("title", " Create New Company Deposit");
                        $template->replace("val_AsycudaNum", $_SESSION['$asycudawrapper']);
                        $template->replace("ASYCUDANum", $this->Asycuda);
                        $template->replace("val_Deposit", $_SESSION['$depamountwrapper']);
                        $template->replace("Deposit", $this->DepAmount);
                        $template->replace("val_CompId", $_SESSION['$compidwrapper']);
                        $template->replace("CompId", $this->CompId);
                        $template->publish();
                    }
                }
            } else
            if ($varid == "") {
                $this->CompId = $varid = $depinst->CompanyId = "";
                $this->Asycuda = $depinst->ASYCUDA = $_POST["ASYCUDANum"];
                $this->DepAmount = $depamount = $_POST["Deposit"];
                $depinst->Comments = $comments = $_POST["Comments"];
                //Do postback with values already entered 
                $template = new MasterTemplate();
                $template->load("Views/Deposit/deposit.html");
                $template->replace("title", " Create New Company Deposit");
                $template->replace("ASYCUDANum", $this->Asycuda);
                $template->replace("Deposit", $this->DepAmount);
                $template->replace("CompId", $this->CompId);
                $template->replace("val_Deposit", "");
                $template->replace("val_AsycudaNum", "");
                $template->replace("val_CompId", '<span style="color:red" >' . " * " . "Please select a company" . '</span>');
                $template->publish();
            }
        } else
        if (isset($_GET)) {
            $template = new MasterTemplate();
            $template->load("Views/Deposit/deposit.html");
            $template->replace("title", " Create New Company Deposit");
            $template->replace("val_Deposit", "");
            $template->replace("val_AsycudaNum", "");
            $template->replace("val_CompId", "");
            $template->replace("Deposit", "");
            $template->replace("ASYCUDANum", "");
            $template->publish();
        }
    }

}
