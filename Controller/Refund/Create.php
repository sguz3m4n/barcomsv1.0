<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Refund.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

static $asycuderr = "";
static $asycudawrapper = "";
static $refamounterr = "";
static $refamountwrapper = "";
static $compiderr = "";
static $compidwrapper = "";

class MakeRefundController extends PermissionController {

    function __construct() {
        $this->setRoles(['Manager', 'Super User']);
    }

    private $Asycuda = "";
    private $AsycudaIsValid = "";
    private $RefAmount = "";
    private $RefAmountIsValid = "";
    private $CompId = "";
    private $CompIdIsValid = "";

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        foreach ($elements as $value) {

            if ($value == "Refund") {
                if ($this->RefAmount <= 0) {
                    $refamounterr = "Refund amount must be greater than $0.00";
                    $_SESSION['$refamountwrapper'] = '<span style="color:red" >' . " * " . $refamounterr . '</span>';
                    $this->RefAmountIsValid = 0;
                } else {
                    $this->RefAmountIsValid = 1;
                    $_SESSION['$refamountwrapper'] = NULL;
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
            $refinst = new \BarcomModel\Refund();
            $audinst = new \BarcomModel\Audit();

            (isset($_POST['CompId']) ? $this->CompId = $varid = $refinst->CompanyId = $_POST['CompId'] : $this->CompId = $varid = $refinst->CompanyId = "");

            if ($varid != "") {
                //If the company has no deposits
                if ($refinst->IfExists($varid) == 0) {
                    $refinst->Comments = $comments = $_POST["Comments"];
                    $refamount = number_format($_POST["Refund"], 2, '.', '');
                    $this->RefAmount = $refinst->RefundAmount = $refamount;

                    $template = new MasterTemplate();
                    $template->load("Views/Refund/refund.html");
                    $template->replace("title", " Create New Company Refund");
                    $template->replace("Refund", $this->RefAmount);
                    $template->replace("CompId", $this->CompId);
                    $template->replace("val_CompId", '<span style="color:red" >' . " * " . "Sorry this entity does not exist on record" . '</span>');
                    $template->replace("val_Refund", "");
                    $template->publish();
                } else if ($refinst->IfExists($varid) == 1) {
                    $refinst->Comments = $comments = $_POST["Comments"];
                    $companyname = $_POST['CompName'];
                    $refamount = number_format($_POST["Refund"], 2, '.', '');
                    $this->RefAmount = $refinst->RefundAmount = $refamount;

                    //check to see if company has adequate balance in order to make refund
                    if ($refinst->HasAdequateBalance($this->CompId, $this->RefAmount)) {
                        $crblnc = $prevamount = number_format($_POST["CompanyBalance"], 2, '.', '');
                        $refinst->PreviousBalance = $prevamount;
                        $curbalance = $refinst->CurrentBalance = number_format($refinst->RefundBalance($refamount, $prevamount), 2, '.', '');
                        $refinst->PreviousBalance = $prevamount = $refinst->CurrentBalance;
                        $refinst->DepositAmount = $refinst->RefundAmount;
                        $refinst->ReEnteredBy = $username;

                        //Send elements to be validated
                        $validateme = ["Refund", "CompId"];
                        $this->ValidationEngine($validateme);

                        //if validation succeeds then commit info to database
                        if (($this->RefAmountIsValid) && ($this->CompIdIsValid)) {
                            $refinst->EditDeposit($varid);
                            if ($refinst->auditok == 1) {
                                $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CMPR');
                                $AudtDesc = 'Refund for ' . $varid;
                                //$TranDesc = 'Refund to ' . $varid . " Amt " . $refamount;
                                $TranDesc = $comments;
                                $User = $username;
                                $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                                $audinst->CreateTransAuditRecord($tranid, $TranDesc, $username, "REF", $refamount, $varid);
                                $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $varid . '</span><br><br><br>' .
                                        '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $companyname . '</span><br><br><br>' .
                                        '<span class="label label-success">Previous Balance</span> ' . '<span class="label label-info"> ' . '$' . number_format($crblnc, '2', '.', ',') . '</span><br><br><br>' .
                                        '<span class="label label-success">Current Balance</span> ' . '<span class="label label-info"> ' . '$' . number_format($curbalance, '2', '.', ',') . '</span><br><br><br>' .
                                        '<span class="label label-success">Refund Amount</span> ' . '<span class="label label-info">' . '$' . number_format($refamount, '2', '.', ',') . '</span><br>';

                                $token1 = 'Record Successfully Created';
                                header("Location:" . "/success?result=$token&header=$token1&args=");
                            }
                        } else {
                            //if validation fails do postback with values already entered 
                            $template = new MasterTemplate();
                            $template->load("Views/Refund/refund.html");
                            $template->replace("title", " Create New Company Refund");
                            $template->replace("val_Refund", $_SESSION['$refamountwrapper']);
                            $template->replace("Refund", $this->RefAmount);
                            $template->replace("val_CompId", $_SESSION['$compidwrapper']);
                            $template->replace("CompId", $this->CompId);
                            $template->publish();
                        }
                    } else {
                        $refinst->Comments = $comments = $_POST["Comments"];
                        $refamount = number_format($_POST["Refund"], 2, '.', '');
                        $template = new MasterTemplate();
                        $template->load("Views/Refund/refund.html");
                        $template->replace("title", " Create New Company Refund");
                        $template->replace("Refund", $this->RefAmount);
                        $template->replace("CompId", $this->CompId);
                        $template->replace("val_CompId", '<span style="color:red" >' . " * " . "Insufficient funds. Please check company balance" . '</span>');
                        $template->replace("val_Refund", "");
                        $template->publish();
                    }
                }
            } else if ($varid == "") {
                $refinst->Comments = $comments = $_POST["Comments"];
                $refamount = number_format($_POST["Refund"], 2, '.', '');
                $template = new MasterTemplate();
                $template->load("Views/Refund/refund.html");
                $template->replace("title", " Create New Company Refund");
                $template->replace("Refund", $this->RefAmount);
                $template->replace("CompId", $this->CompId);
                $template->replace("val_CompId", '<span style="color:red" >' . " * " . "Please select a company" . '</span>');
                $template->replace("val_Refund", "");

                $template->publish();
            }
        } else
        if (isset($_GET)) {
            $template = new MasterTemplate();
            $template->load("Views/Refund/refund.html");
            $template->replace("title", " Create New Company Refund");
            $template->replace("val_Refund", "");
            $template->replace("val_CompId", "");
            $template->replace("Refund", "");
            $template->publish();
        }
    }

    //put your code here
}
