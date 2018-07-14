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
require 'Classes/Payment.php';
require 'Classes/Deposit.php';
require 'Classes/Audit.php';
require 'Classes/Employee.php';
require 'Controller/base_template.php';
require 'Controller/Success/Show.php';

static $PRNerr = "";
static $PRNwrapper = "";
static $startdateerr = "";
static $startdatewrapper = "";
static $enddateerr = "";
static $enddatewrapper = "";
static $REFerr = "";
static $REFwrapper = "";
static $Hourserr = "";
static $Hourswrapper = "";
static $PassNumerr = "";
static $PassNumwrapper = "";
static $Distanceerr = "";
static $Distancewrapper = "";
static $selfiderr = "";
static $selfidwrapper = "";
static $empiderr = "";
static $empidwrapper = "";
static $companybalerr = "";
static $companybalwrapper = "";
static $insufferr = "";
static $insuffwrapper = "";

class MakePaymentController extends PermissionController {

    function __construct() {
        $this->setRoles(['Payment Clerk', 'Manager', 'Super User']);
    }

    private $BillFormNo;
    private $PRN = "";
    protected $PRNIsValid = "";
    private $InspectionDate = "";
    private $StartDate = "";
    private $EndDate = "";
    private $BillRef = "";
    private $Hours = "";
    private $Distance = "";
    private $PassNum = "";
    private $CompId = "";
    protected $SelfIsValid = "";
    protected $EmpId = "";
    protected $CompanyBalance = "";
    protected $CompanyBalanceIsValid = "";
    protected $National_Id;
    private $OTEarned;
    protected $TotalAmountEarned;
    protected $IsSufficient;
    private $IsPRN;
    private $PassSubsidy;
    private $HasPymtRecs;
    private $MyPaymentsRecords;
    private $token;
    private $GrandTotal;

    //Validation Engine will execute any validation on the fields in the interface
    function ValidationEngine($elements) {
        $pymnt = new \BarcomModel\Payment();
        $_SESSION['$PRNwrapper'] = NULL;
        $_SESSION['$companybalwrapper'] = NULL;
        $_SESSION['$selfidwrapper'] = NULL;
        $_SESSION['$insuffwrapper'] = NULL;
        $_SESSION['$norecwrapper'] = NULL;
        foreach ($elements as $value) {
            if ($value == "PRNNo") {
                if ($pymnt->PRNChecker($this->PRN) == 0) {
                    $PRNerr = "PRN number is invalid";
                    $_SESSION['$PRNwrapper'] = '<span style="color:red" >' . " * " . $PRNerr . '</span>';
                    $this->PRNIsValid = 0;
                } else {
                    $this->PRNIsValid = 1;
                }
            }
            if ($value == "CompanyBalance") {
                if (($this->CompanyBalance <= 0) || ($this->CompanyBalance == NULL)) {
                    $companybalerr = "Sorry payment cannot be made with zero balance";
                    $_SESSION['$companybalwrapper'] = '<span style="color:red" >' . " * " . $companybalerr . '</span>';
                    $this->CompanyBalanceIsValid = 0;
                } else {
                    $this->CompanyBalanceIsValid = 1;
                }
            }
            if ($value == "SelfPay") {
                if (($this->National_Id == $this->EmpId)) {
                    $selfiderr = "Self payment is not allowed...";
                    $_SESSION['$selfidwrapper'] = '<span style="color:red" >' . " * " . $selfiderr . '</span>';
                    $this->SelfIsValid = 0;
                } else {
                    $this->SelfIsValid = 1;
                }
            }
            if ($value == "Sufficient") {
                if (($this->TotalAmountEarned > $this->CompanyBalance)) {
                    $insufferr = "Cannot proceed with payment. Insufficient funds...";
                    $_SESSION['$insuffwrapper'] = '<span style="color:red" >' . " * " . $insufferr . '</span>';
                    $this->IsSufficient = 0;
                } else {
                    $this->IsSufficient = 1;
                }
            }
            if ($value == "HasRecords") {
                if (empty($this->MyPaymentsRecords)) {
                    $norecerr = "Please add officer before making payment...";
                    $_SESSION['$norecwrapper'] = '<span style="color:red" >' . " * " . $norecerr . '</span>';
                    $this->HasPymtRecs = 0;
                } else {
                    $this->HasPymtRecs = 1;
                }
            }
        }
    }

    //Validation Engine will execute any validation on the fields in the interface

    function show($params) {
        $username = $_SESSION["login_user"];
        $this->National_Id = $_SESSION['NatReg'];
        $test = new \BarcomModel\PayScale();
        if (isset($_POST['btn-create'])) {

            $this->MyPaymentsRecords = $pymntrecs = json_decode($_POST['paylist'], TRUE);
            $varhaspx = 'off';
            $varhassubs = 'off';
            $count = 0;

            $employee = new \BarcomModel\Employee();
            $this->BillRef = $_POST['BillRef'];

            if ($_POST['PRNNo'] == "") {
                $this->IsPRN = 0;
                $this->BillFormNo = $_POST['BillFormNo'];
            } else
            if (!$_POST['PRNNo'] == "") {
                $this->IsPRN = 1;
                $this->PRN = $_POST['PRNNo'];
                $this->BillFormNo = $_POST['BillFormNo'];
            }

            $StartDate = $this->StartDate = $_POST['InspectionDateStart'];
            $this->EndDate = $_POST['InspectionDateEnd'];
            $this->InspectionDate = $_POST['ProcessDate'];
            $this->CompanyBalance = $_POST['CompanyBalance'];
            $this->CompId = $_POST['CompId'];

            $validateme = ["HasRecords"];
            $this->ValidationEngine($validateme);

            if ($this->HasPymtRecs) {
                //foreach loop to iterate through all array elements
                foreach ($pymntrecs as $value) {

                    $indvemployee = new \BarcomModel\Employee();
                    $indvpymtinst = new \BarcomModel\Payment();

                    $companyname = $_POST['CompName'];
                    $employeename = $value[0];
                    $this->EmpId = $varid = $indvpymtinst->OfficerId = $value[1];
                    $employeeDetails = $indvemployee->GetEmployee($varid);
                    $indvpymtinst->PayRate = $payrate = $value[6];
                    $indvpymtinst->RateCode = $employeeDetails[0]["RateCode"];
                    $this->Hours = $varhours = $indvpymtinst->HoursWorked = $value[2];
                    $this->Distance = $vardistance = $indvpymtinst->Distance = $value[3];
                    ($value[8] === "on" ? $varhaspx = "on" : $varhaspx);
                    ($value[9] === "on" ? $varhassubs = "on" : $varhassubs);

                    //CALCULATE SUBSISTENCE AMOUNT OR NOT- CURRENT VALUE IS $25.00
                    if ($varhassubs === 'on') {
                        $varhassubs = TRUE;
                        $indvpymtinst->Subsistence = "Y";
                        $indvpymtinst->SubsistenceAmount = $varSubsistenceEarned = number_format($value[5], 2, '.', '');
                    } else {
                        $indvpymtinst->Subsistence = "N";
                        $varSubsistenceEarned = $indvpymtinst->SubsistenceAmount = number_format(0, 2, '.', '');
                    }
                    //CALCULATE DISTANCE VALUE OR NOT 
                    if ($vardistance > 0) {
                        if ($varhaspx === 'on') {
                            $varhaspx = TRUE;
                            $indvpymtinst->Passengers = "Y";
                            $this->PassNum = $indvpymtinst->NumberPx = $value[4];
                            //$subsidy = $this->PassSubsidy = 0.00;
                            /* if ($this->PassNum == 1) {
                              $subsidy = $this->PassSubsidy = 2.19;
                              } else
                              if ($this->PassNum > 1) {
                              $subsidy = $this->PassSubsidy = 2.28;
                              } */
                        } else {
                            $varhaspx = FALSE;
                            $indvpymtinst->Passengers = "N";
                            $this->PassNum = $indvpymtinst->NumberPx = 0;
                            //$subsidy = $this->PassSubsidy = number_format(0, 2, '.', '');
                        }
                        $indvpymtinst->TravelAmount = $varTravelEarned = number_format($indvpymtinst->DistanceEarned($vardistance, $varhaspx), 2, '.', '');
                    } else {
                        $indvpymtinst->TravelAmount = number_format(0, 2, '.', '');
                        $indvpymtinst->Distance = number_format(0, 2, '.', '');
                        $varTravelEarned = number_format(0, 2, '.', '');
                        $varhaspx = FALSE;
                        $indvpymtinst->Passengers = "N";
                        $this->PassNum = $indvpymtinst->NumberPx = 0;
                        //$subsidy = $this->PassSubsidy = number_format(0, 2, '.', '');
                    }

                    //CALCULATE OVERTIME PAYMENT
                    if ($test->IsWeekend($StartDate)) {
                        $this->OTEarned = $indvpymtinst->OvertimeAmount = $varOTEarned = $indvpymtinst->OTEarned(1, $payrate);
                    } else
                    if ($test->IsHoliday($StartDate)) {
                        $this->OTEarned = $indvpymtinst->OvertimeAmount = $varOTEarned = $indvpymtinst->OTEarned($varhours, $payrate);
                    } else
                    if ((!$test->IsWeekend($StartDate)) && (!$test->IsHoliday($StartDate))) {
                        //if ($varhours > 0) {
                            $this->OTEarned = $indvpymtinst->OvertimeAmount = $varOTEarned = $indvpymtinst->OTEarned($varhours, $payrate);
                        //}
                    }


                    //CALCULATE/SET OTHER AMOUNTS
                    $indvpymtinst->RefundAmount = $indvpymtinst->HolidayAmount = $indvpymtinst->OtherAmount = $indvpymtinst->NISDeductions = $indvpymtinst->PAYEDeductions = number_format(0, 2, '.', '');

                    //CALCULATE TOTAL AMOUNT TO BE PAID
                    //$this->TotalAmountEarned = $indvpymtinst->TotalAmount = $total = $varTravelEarned + $varSubsistenceEarned + $varOTEarned + $subsidy;
                    $this->TotalAmountEarned = $indvpymtinst->TotalAmount = $total = $varTravelEarned + $varSubsistenceEarned + $varOTEarned;
                    $this->GrandTotal = $this->TotalAmountEarned + $this->GrandTotal;
                    //Send elements to be validated
                    if ($this->IsPRN == 1) {
                        $validateme = ["PRNNo", "CompanyBalance", "SelfPay", "Sufficient"];
                    } else
                    if ($this->IsPRN == 0) {
                        $validateme = ["CompanyBalance", "SelfPay", "Sufficient"];
                        $_SESSION['$PRNwrapper'] = NULL;
                    }

                    $this->ValidationEngine($validateme);
                    //this is to override the validation in order to process the payment
                    if (!$this->IsPRN) {
                        $this->PRNIsValid = 1;
                    }

                    //if validation succeeds then commit info to database
                    if (($this->PRNIsValid) && ($this->SelfIsValid) && ($this->CompanyBalanceIsValid) && ($this->IsSufficient) && ($this->HasPymtRecs)) {
                        $tranid = $indvpymtinst->TranId = $indvpymtinst->GenerateTimestamp('PYMT');
                        $tranid = $tranid . $count;
                        $indvpymtinst->TranType = 'PYMT';
                        $indvpymtinst->RecEnteredBy = $username;

                        $indvpymtinst->CreatePaymentTransaction($tranid, $this->CompId, $this->BillFormNo, $this->BillRef, $this->InspectionDate, $this->StartDate, $this->EndDate);

                        if ($indvpymtinst->auditok == 1) {

                            $depinst = new \BarcomModel\Deposit();
                            $audinst = new \BarcomModel\Audit();

                            $depinst->ReEnteredBy = $username;


                            //$tranid = $audinst->TranId = $audinst->GenerateTimestamp('PYMT');
                            //$tranid = $tranid . $count;
                            $AudtDesc = 'Payment for ' . $varid;
                            $TranDesc = 'Payment to ' . $varid . " Amt " . $total;
                            $User = $username;
                            $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                            $audinst->CreateTransAuditRecord($tranid, $AudtDesc, $User, "PMT", $total, $varid);

                            if ($this->IsPRN) {
                                $pymt = new \BarcomModel\Payment();
                                $pymt->PRNSetter($this->PRN, $User);
                            }
                            $depinst->MakePayment($this->CompanyBalance, $this->GrandTotal, $this->CompId);
                            
                        }
                    } else {
                        //if validation fails do postback with values already entered
                        $template = new MasterTemplate();
                        $template->load("Views/Payment/payment.html");
                        $template->replace("title", "Create New Bill Payment");
                        $template->replace("val_PRN", $_SESSION['$PRNwrapper']);
                        $template->replace("val_CompanyBalance", $_SESSION['$companybalwrapper']);
                        $template->replace("val_SelfPay", $_SESSION['$selfidwrapper']);
                        $template->replace("val_Insufficient", $_SESSION['$insuffwrapper']);
                        $template->replace("val_NoRecords", $_SESSION['$norecwrapper']);
                        $template->publish();
                    }
                     $token1 = 'Record Successfully Created <br>' . 'Company Name: ' . $companyname . '<br>Total Amount: ' . '$' . number_format($this->GrandTotal, 2, '.', '');
                     $this->token = $this->token . ' <tr>'
                            . '<td>' . $varid . '</td>'
                            . '<td>' . $employeename . '</td>'
                            . '<td>' . $varOTEarned . '</td>'
                            . '<td>' . $varTravelEarned . '</td>'
                            . '<td>' . $varSubsistenceEarned . '</td>'
                            . '<td>' . '$' . number_format($total, 2, '.', '') . '</td>'
                            . '</tr>';
                    $count++;
                }
                    if (!headers_sent()) {
                     header("Location:" . "/success?result=$this->token&header=$token1&args=table");
                     exit;
                    }
        
                //end foreach 
            } else {
                $template = new MasterTemplate();
                $template->load("Views/Payment/payment.html");
                $template->replace("title", "Create New Bill Payment");
                $template->replace("val_PRN", $_SESSION['$PRNwrapper']);
                $template->replace("val_CompanyBalance", $_SESSION['$companybalwrapper']);
                $template->replace("val_SelfPay", $_SESSION['$selfidwrapper']);
                $template->replace("val_NoRecords", $_SESSION['$norecwrapper']);
                $template->replace("val_Insufficient", $_SESSION['$insuffwrapper']);
                $template->publish();
            }
        } else
        if (isset($_GET)) {

            $template = new MasterTemplate();
            $template->load("Views/Payment/payment.html");
            $template->replace("title", "Create New Bill Payment");
            $template->replace("val_PRN", "");
            $template->replace("val_SelfPay", "");
            $template->replace("val_CompanyBalance", "");
            $template->replace("val_Insufficient", "");
            $template->replace("val_NoRecords", "");
            $template->publish();
        }
    }

}

class OfficerPaymentController extends MakePaymentController {

    function __construct() {
        $this->setRoles(['Officer', 'Managers', 'SuperUser']);
    }

    /* function Validation($elements) {
      $pymnt = new \BarcomModel\Payment();
      $errors = " ";
      foreach ($elements as $value) {
      if ($value == "PRNNo") {
      if ($pymnt->PRNChecker($this->PRN) == 0) {
      $errors = $errors . "PRN number is invalid";
      }
      }
      if ($value == "CompanyBalance") {
      if (($this->CompanyBalance <= 0) || ($this->CompanyBalance == NULL)) {
      $errors = $errors . " Sorry payment cannot be made with zero balance";
      }
      }

      if ($value == "SelfPay") {
      if (($this->National_Id == $this->EmpId)) {
      $errors = $errors."Self payment is not allowed...";
      }
      }
      if ($value == "Sufficient") {
      if (($this->TotalAmountEarned > $this->CompanyBalance)) {
      $errors = $errors . " Cannot proceed with payment. Insufficient funds...";
      }
      }
      }
      return $errors;
      }
     */

    function show($params) {
        $natregno = $_SESSION['NatReg'];
        $username = $_SESSION["login_user"];

        $varhaspx = 'off';
        $varhassubs = 'off';

        $employee = new \BarcomModel\Employee();
        $employeeDetails = $employee->GetEmployee($natregno);
        $alerts = "";

        if (isset($_POST["btn-create"])) {
            $payment = new \BarcomModel\Payment();
            $audit = new \BarcomModel\Audit();

            //$payment->CompanyName = $_POST["CompName"];
            $payment->InspectionDate = $payment->StartDate = $_POST["InspectionDateStart"];
            $payment->EndDate = $_POST["InspectionDateEnd"];
            $payment->Distance = $_POST["distance"];
            $payment->HoursWorked = $_POST["hours"];

            $compid = $payment->CompanyId = $_POST["CompId"];
            $payment->CompanyAddress = $_POST["address"];
            $companyname = $payment->CompanyName = $_POST["CompName"];

            $payment->OfficerId = $natregno;
            //$payment->National_Id = $natregno; //Not sure we need this
            //$payment->EmpId = 0; //Not sure we need this
            $payment->RateCode = $employeeDetails[0]["RateCode"];

            $payment->RefundAmount = number_format(0, 2, '.', '');
            $payment->HolidayAmount = number_format(0, 2, '.', '');
            $payment->OtherAmount = number_format(0, 2, '.', '');
            $payment->TravelAmount = number_format(0, 2, '.', '');
            $payment->OvertimeAmount = number_format(0, 2, '.', '');
            $payment->SubsistenceAmount = number_format(0, 2, '.', '');
            $payment->NISDeductions = number_format(0, 2, '.', '');
            $payment->PAYEDeductions = number_format(0, 2, '.', '');
            $total = $payment->TotalAmount = number_format(0, 2, '.', '');

            //$this->REF = $payment->BllFormNo = $_POST['billRef'];
            //$this->CompanyBalance = $payment->CompanyBalance = 0; //$_POST['entityBalance']; TODO ADD back in when company fixed
            (isset($_POST['passenger']) ? $varhaspx = "on" : $varhaspx);

            (isset($_POST['subsistence']) ? $varhassubs = "on" : $varhassubs);

            (isset($_POST['PRN']) ? $this->PRN = $payment->PRN = $_POST['PRN'] : $payment->PRN = "" );


            //CALCULATE SUBSISTENCE AMOUNT OR NOT- CURRENT VALUE IS $25.00
            if ($varhassubs === 'on') {
                $varhassubs = TRUE;
                $payment->Subsistence = "Y";
            } else {
                $payment->Subsistence = "N";
                $payment->SubsistenceAmount = number_format(0, 2, '.', '');
            }

            if ($varhaspx === 'on') {
                $varhaspx = TRUE;
                $payment->Passengers = "Y";
                $this->PassNum = $payment->NumberPx = $_POST['passnum'];
            } else {
                $varhaspx = FALSE;
                $payment->Passengers = "N";
                $this->PassNum = $payment->NumberPx = 0;
            }

            //Validate and create record if applicable
            /* $alerts = $this->Validation(["PRNNo", "CompanyBalance",
              "SelfPay", "Sufficent"]); */
            if ($alerts == "") {
                $tranid = $payment->TranId = $payment->GenerateTimestamp('APPL');
                $payment->TranType = 'APPL';
                $payment->RecEnteredBy = $username;
                $payment->CreatePaymentTransaction();

                $AudtDesc = 'Application for ' . $payment->OfficerId;
                //$TranDesc = 'Application for ' . $payment->OfficerId . " Amt " . $total;
                //$audit->CreateUserAuditRecord($tranid, $username, $AudtDesc);
                //$audit->CreateTransAuditRecord($tranid, $AudtDesc, $username, "APL", $total, $payment->OfficerId);
                //$alerts = "Officer Report Entered Successfully";
                $template = new BaseTemplate();
                $template->load("Views/Payment/officersuccess.html");
                $token = '<br><br><span class="label label-success">Employee National Registration #</span> ' . '<span class="label label-info">' . $natregno . '</span><br><br><br>' .
                        '<span class="label label-success">Employee Name</span> ' . '<span class="label label-info">' . $username . '</span><br><br><br>' .
                        '<span class="label label-success">Company Name</span> ' . '<span class="label label-info">' . $companyname . '</span><br><br><br>' .
                        '<span class="label label-success">Company Id #</span> ' . '<span class="label label-info">' . $compid . '</span><br>';
                $template->replace("token1", 'Record Successfully Created');
                $template->replace("token2", $token);
                $template->publish();
            }
        } else {
            $template = new BaseTemplate();
            $template->load("Views/Payment/officer.html");
            $template->replace("title", "Officer Hour Report");
            $template->replace("first_name", $_SESSION["firstname"]);
            $template->replace("alerts", $alerts);
            $template->publish();
        }
    }

}
