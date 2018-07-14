<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Create.php';

class AmendPaymentController extends MakePaymentController {

    private $InspectionDate = "";
    private $StartDate = "";
    private $EndDate = "";
    private $Distance = "";
    private $PassNum = "";
    private $CompId = "";
    private $OTEarned;
    private $PassSubsidy;
    private $GrandTotal;

    function show($params) {
        $username = $_SESSION["login_user"];
        $this->National_Id = $_SESSION['NatReg'];
        if (isset($_POST['submit-amendment'])) {
            $varhaspx = 'off';
            $varhassubs = 'off';
            $empinst = new \BarcomModel\Employee();
            $payinst = new \BarcomModel\Payment();
            $tranid = $payinst->GenerateTimestamp("AMND");
            $BillRef = $_POST['RN'];
            $BillFormNo = $_POST['BillRefNo'];
            $this->CompId = $payinst->CompanyId = $_POST['compid'];
            $this->EmpId = $natregno = $payinst->OfficerId = $_POST['Natregno'];
            $employeeDetails = $empinst->GetEmployee($natregno);
            $payinst->PayRate = $payrate = $employeeDetails[0]["RateAmount"];
            $employeename = $employeeDetails[0]["FirstName"] . ' ' . $employeeDetails[0]["LastName"];
            $payinst->RateCode = $employeeDetails[0]["RateCode"];
            $this->InspectionDate = $_POST['InspectionDate'];
            $this->StartDate = $_POST['StartDate'];
            $this->EndDate = $_POST['EndDate'];
            $this->CompanyBalance = $_POST['compbal'];
            $companyname = $payinst->CompanyName = $_POST["compname"];

            $varhours = $payinst->HoursWorked = $_POST['hours'];
            $vardistance = $payinst->Distance = $_POST['distance'];
            if (isset($_POST['passengers'])) {
                ($_POST['passengers'] === "on" ? $varhaspx = "on" : $varhaspx);
            }
            if (isset($_POST['subsistence'])) {
                ($_POST['subsistence'] === "on" ? $varhassubs = "on" : $varhassubs);
            }

            //CALCULATE SUBSISTENCE AMOUNT OR NOT- CURRENT VALUE IS $25.00
            if ($varhassubs === 'on') {
                $varhassubs = TRUE;
                $payinst->Subsistence = "Y";
                $payinst->SubsistenceAmount = $varSubsistenceEarned = number_format($_POST['subamt'], 2, '.', '');
            } else {
                $payinst->Subsistence = "N";
                $varSubsistenceEarned = $payinst->SubsistenceAmount = number_format(0, 2, '.', '');
            }

            //CALCULATE DISTANCE VALUE OR NOT 
            if ($vardistance > 0) {
                if ($varhaspx === 'on') {
                    $varhaspx = TRUE;
                    $payinst->Passengers = "Y";
                    $this->PassNum = $payinst->NumberPx = $_POST['passnum'];
                       $subsidy = $this->PassSubsidy = 0.00;
                    /*if ($this->PassNum == 1) {
                        $subsidy = $this->PassSubsidy = 2.19;
                    } else
                    if ($this->PassNum > 1) {
                        $subsidy = $this->PassSubsidy = 2.28;
                    }*/
                } else {
                    $varhaspx = FALSE;
                    $payinst->Passengers = "N";
                    $this->PassNum = $payinst->NumberPx = 0;
                    $subsidy = $this->PassSubsidy = number_format(0, 2, '.', '');
                }
                $payinst->TravelAmount = $varTravelEarned = number_format($payinst->DistanceEarned($vardistance, $varhaspx), 2, '.', '');
            } else {
                $payinst->TravelAmount = number_format(0, 2, '.', '');
                $payinst->Distance = number_format(0, 2, '.', '');
                $varTravelEarned = number_format(0, 2, '.', '');
                $varhaspx = FALSE;
                $payinst->Passengers = "N";
                $this->PassNum = $payinst->NumberPx = 0;
                $subsidy = $this->PassSubsidy = number_format(0, 2, '.', '');
            }

            //CALCULATE OVERTIME PAYMENT
            $this->OTEarned = $payinst->OvertimeAmount = $varOTEarned = ($varhours > 0 ? $payinst->OTEarned($varhours, $payrate) : '0.00');

            //CALCULATE/SET OTHER AMOUNTS
            $payinst->RefundAmount = $payinst->HolidayAmount = $payinst->OtherAmount = $payinst->NISDeductions = $payinst->PAYEDeductions = number_format(0, 2, '.', '');

            //CALCULATE TOTAL AMOUNT TO BE PAID
            $this->TotalAmountEarned = $payinst->TotalAmount = $total = $varTravelEarned + $varSubsistenceEarned + $varOTEarned + $subsidy;
            $this->GrandTotal = $this->TotalAmountEarned + $this->GrandTotal;

            $validateme = ["CompanyBalance", "SelfPay", "Sufficient"];
            $this->ValidationEngine($validateme);
            //if validation succeeds then commit info to database
            if (($this->SelfIsValid) && ($this->CompanyBalanceIsValid) && ($this->IsSufficient)) {

                $payinst->TranType = 'AMND';
                $payinst->RecEnteredBy = $username;

                $payinst->CreatePaymentTransaction($tranid, $this->CompId, $BillFormNo, $BillRef, $this->InspectionDate, $this->StartDate, $this->EndDate);

                if ($payinst->auditok == 1) {

                    $depinst = new \BarcomModel\Deposit();
                    $audinst = new \BarcomModel\Audit();

                    $depinst->ReEnteredBy = $username;
                    $depinst->MakePayment($this->CompanyBalance, $payinst->TotalAmount, $this->CompId);

                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('AMND');
                    $AudtDesc = 'Payment for ' . $natregno;
                    $TranDesc = 'Payment to ' . $natregno . " Amt " . $total;
                    $User = $username;
                    $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                    $audinst->CreateTransAuditRecord($tranid, $AudtDesc, $User, "PMT", $total, $natregno);

                    $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $this->CompId . '</span><br><br><br>' .
                            '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $companyname . '</span><br><br><br>' .
                            '<span class="label label-success">Employee Name</span> ' . '<span class="label label-info"> ' . $employeename . '</span><br><br><br>' .
                            '<span class="label label-success">Employee Id</span> ' . '<span class="label label-info"> ' . $natregno . '</span><br><br><br>' .
                            '<span class="label label-success">Travel Amount</span> ' . '<span class="label label-info"> ' . $varTravelEarned . '</span><br><br><br>' .
                            '<span class="label label-success">Subsistence Amount</span> ' . '<span class="label label-info"> ' . $varSubsistenceEarned . '</span><br><br><br>' .
                            '<span class="label label-success">Overtime Amount</span> ' . '<span class="label label-info"> ' . $varOTEarned . '</span><br><br><br>' .
                            '<span class="label label-success">Subsidy Amount</span> ' . '<span class="label label-info"> ' . $subsidy . '</span><br><br><br>' .
                            '<span class="label label-success">Total Amount</span> ' . '<span class="label label-info"> ' . $total . '</span><br><br><br>';

                    $token1 = 'Record Successfully Created <br>' . 'Company Name: ' . $companyname . '<br>Total Amount: ' . '$' . number_format($this->GrandTotal, 2, '.', '');
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            } else {
                $template = new MasterTemplate();
                $template->load("Views/Payment/edit.html");
                $template->replace("title", "Create New Bill Payment");
                $template->replace("val_CompanyBalance", $_SESSION['$companybalwrapper']);
                $template->replace("val_SelfPay", $_SESSION['$selfidwrapper']);
                $template->replace("val_Insufficient", $_SESSION['$insuffwrapper']);
                $template->publish();
            }
        } else
        if (isset($_GET)) {

            $template = new MasterTemplate();
            $template->load("Views/Payment/edit.html");
            $template->replace("title", "Create New Bill Payment");
            $template->replace("val_SelfPay", "");
            $template->replace("val_CompanyBalance", "");
            $template->replace("val_Insufficient", "");
            $template->publish();
        }
    }

}

?>
