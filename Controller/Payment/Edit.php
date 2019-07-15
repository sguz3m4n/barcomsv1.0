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

class EditPaymentController extends MakePaymentController {

    private $Distance = "";
    private $PassNum = "";
    private $CompId = "";
    private $OTEarned;
    private $PassSubsidy;
    private $GrandTotal;
    private $NewCompBal;

    function show($params) {
        $username = $_SESSION["login_user"];
        $this->National_Id = $_SESSION['NatReg'];
        if (isset($_POST['update'])) {
            $varhaspx = 'off';
            $varhassubs = 'off';
            $empinst = new \BarcomModel\Employee();
            $payinst = new \BarcomModel\Payment();
            $depinst = new \BarcomModel\Deposit();
            $audinst = new \BarcomModel\Audit();

            $tranid = $payinst->TranId = $_POST["transid"];
            $compid = $this->CompId = $payinst->CompanyId = $_POST['compid'];
            $this->EmpId = $natregno = $payinst->OfficerId = $_POST['natregno'];
            $employeeDetails = $empinst->GetEmployee($natregno);
            $payinst->PayRate = $payrate = $employeeDetails[0]["RateAmount"];
            $employeename = $employeeDetails[0]["FirstName"] . ' ' . $employeeDetails[0]["LastName"];

            $payinst->RateCode = $employeeDetails[0]["RateCode"];
            $compbal = $this->CompanyBalance = $_POST['compbal'];
            $companyname = $payinst->CompanyName = $_POST["compname"];

            $varhours = $payinst->HoursWorked = $_POST['hours'];
            $vardistance = $payinst->Distance = $_POST['distance'];
            $refundamt = $_POST['refund'];

            if (isset($_POST['passengers'])) {
                ($_POST['passengers'] === "on" ? $varhaspx = "on" : $varhaspx);
            }
            if (isset($_POST['subsistence'])) {
                ($_POST['subsistence'] === "on" ? $varhassubs = "on" : $varhassubs);
            }
            if ($varhours != NULL) {
                $this->OTEarned = $payinst->OvertimeAmount = $varOTEarned = $payinst->OTEarned($varhours, $payrate);
            }

            //CALCULATE SUBSISTENCE AMOUNT OR NOT- CURRENT VALUE IS $25.00
            if ($varhassubs === 'on') {
                $varhassubs = TRUE;
                $payinst->Subsistence = "Y";
                $payinst->SubsistenceAmount = $varSubsistenceEarned = number_format($_POST['subamt'], 2, '.', '');
                //$payinst->SubsistenceAmount = $varSubsistenceEarned = number_format($payinst->SubsistenceEarned($varhassubs), 2, '.', '');
            } else {
                $payinst->Subsistence = "N";
                $varSubsistenceEarned = $payinst->SubsistenceAmount = number_format(0, 2, '.', '');
            }
            //CALCULATE DISTANCE VALUE OR NOT 
            if ($vardistance != NULL) {
                if ($varhaspx === 'on') {
                    $varhaspx = TRUE;
                    $payinst->Passengers = "Y";
                    $this->PassNum = $payinst->NumberPx = $_POST['passnum'];
                    // $subsidy = $this->PassSubsidy = 0.00;
                    /* if ($this->PassNum == 1) {
                      $this->PassSubsidy = 2.19;
                      }
                      if ($this->PassNum > 1) {
                      $this->PassSubsidy = 2.28;
                      } */
                } else {
                    $varhaspx = FALSE;
                    $payinst->Passengers = "N";
                    $this->PassNum = $payinst->NumberPx = 0;
                    $this->PassSubsidy = 0.00;
                }
                $payinst->TravelAmount = $varTravelEarned = number_format($payinst->DistanceEarned($vardistance, $varhaspx), 2, '.', '');
            } else {
                $payinst->TravelAmount = number_format(0, 2, '.', '');
                $payinst->Distance = number_format(0, 2, '.', '');
                $varTravelEarned = number_format(0, 2, '.', '');
                $this->PassNum = $payinst->NumberPx = 0;
                $this->PassSubsidy = 0.00;
            }

            $this->TotalAmountEarned = $payinst->TotalAmount = $total = $varTravelEarned + $varSubsistenceEarned + $varOTEarned + $this->PassSubsidy;
            $this->GrandTotal = $this->TotalAmountEarned + $this->GrandTotal;

            $validateme = ["CompanyBalance", "SelfPay", "Sufficient"];
            $this->ValidationEngine($validateme);
            if (($this->CompanyBalanceIsValid) && ($this->IsSufficient) && ($this->SelfIsValid)) {
                //Reverse payment already made
                $depinst->PreviousBalance = $compbal;
                $this->NewCompBal = $compbal + ($refundamt * 2);
                //Reverse payment already made
                $payinst->UpdatePaymentTransaction($tranid);
                $AudtDesc = 'Update Payment for ' . $compid . ' Amt ' . $total;
                #$TranDesc = 'Update Payment to ' . $compid . ' Amt ' . $total;
                $User = $username;
                if ($payinst->auditok == 1) {
                    //Reverse payment already made
                    $depinst->ReturnMakePayment($this->NewCompBal, $refundamt, $this->GrandTotal, $compid);
                    //$depinst->MakePayment($depinst->CompanyBalance, $total, $compid);
                    //Reverse payment already made
                    $audinst->UpdateUserAuditRecord($tranid, $User, $AudtDesc);
                    $audinst->UpdateTransAuditRecord($tranid, $AudtDesc, $User, "PMT", $total, $compid);

                    $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $this->CompId . '</span><br><br><br>' .
                            '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $companyname . '</span><br><br><br>' .
                            '<span class="label label-success">Employee Name</span> ' . '<span class="label label-info"> ' . $employeename . '</span><br><br><br>' .
                            '<span class="label label-success">Employee Id</span> ' . '<span class="label label-info"> ' . $natregno . '</span><br><br><br>' .
                            '<span class="label label-success">Travel Amount</span> ' . '<span class="label label-info"> ' . $varTravelEarned . '</span><br><br><br>' .
                            '<span class="label label-success">Subsistence Amount</span> ' . '<span class="label label-info"> ' . $varSubsistenceEarned . '</span><br><br><br>' .
                            '<span class="label label-success">Overtime Amount</span> ' . '<span class="label label-info"> ' . $varOTEarned . '</span><br><br><br>' .
                            // '<span class="label label-success">Subsidy Amount</span> ' . '<span class="label label-info"> ' . $subsidy . '</span><br><br><br>' .
                            '<span class="label label-success">Total Amount</span> ' . '<span class="label label-info"> ' . $total . '</span><br><br><br>';

                    $token1 = 'Record Successfully Edited <br>' . 'Company Name: ' . $companyname . '<br>Total Amount: ' . '$' . number_format($this->GrandTotal, 2, '.', '');
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            } else {
                $template = new MasterTemplate();
                $template->load("Views/Payment/edit.html");
                $template->replace("title", "Create New Bill Payment");
                $template->replace("val_CompanyBalance", $_SESSION['$companybalwrapper']);
                $template->replace("val_Insufficient", $_SESSION['$insuffwrapper']);
                $template->replace("val_SelfPay", $_SESSION['$selfidwrapper']);
                $template->publish();
            }
        } else
        if (isset($_GET)) {

            $template = new MasterTemplate();
            $template->load("Views/Payment/edit.html");
            $template->replace("title", "Create New Bill Payment");
            $template->replace("val_CompanyBalance", "");
            $template->replace("val_Insufficient", "");
            $template->publish();
        }
    }

}

class PaymentTableController extends MakePaymentController {

    function show($params) {
        $roles = $_SESSION["user_roles"];
        $canUnlock = in_array($roles, array('Administrator', 'Manager', 'Super User')) ? true : false;
        $unlock = $canUnlock == 1 ? "<th>Unlock</th>" : "";
        $table = "";

        $pymtinst = new \BarcomModel\Payment();
        $filterBy = array();

        if (isset($_REQUEST['compname'])) {
            $filterBy['companyName'] = "B.CompanyName='" . $_REQUEST['compname'] . "'";
        }

        if (isset($_REQUEST['empid'])) {
            $filterBy['employeeName'] = "concat_ws(' ',C.FirstName,C.LastName)  LIKE '" . $_REQUEST['empid'] . "'";
        }

        if (isset($_REQUEST['enddate'])) {
            $filterBy['endDate'] = "DATE(A.EndDate) <='" . $_REQUEST['enddate'] . "'";
        }

        if (isset($_REQUEST['startdate'])) {
            $filterBy['startDate'] = "DATE(A.StartDate) >='" . $_REQUEST['startdate'] . "'";
        }

        $filterBy['status'] = " AND A.Status='Active'";
        $results = $pymtinst->GetFilteredPayments($filterBy);

        if (empty($results)) {
            $table = "<tr>Error fetching results</tr>";
        } else {
            foreach ($results as $value) {
                if ($value["CloseDate"] != '') {
                    $rowClass = 'class="active"';

                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' disabled id=" . $value["RN"] . ">Closed</button></td>";
                    if ($canUnlock) {
                        $unlockButton = "<td><button type='button' class='btn btn-info btn-sm unlock' id=" . $value["TransId"] . "><span class='glyphicon glyphicon-lock'></span></button></td>";
                        $buttonCode = $buttonCode . $unlockButton;
                    }
                } else {
                    $rowClass = "class='danger'";
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' data-toggle='modal' id=" . $value["TransId"] . ">Edit</button></td>";
                    $AmendButton = "<td><button type='button' class='btn btn-info btn-sm amend' data-toggle='modal' id=" . $value["BillRefNo"] . ">Amend</button></td>";
                    $AmendButton = "<td><button type='button' class='btn btn-info btn-sm amend'  id=" . $value["BillRefNo"] . ">Amend</button></td>";
                }

                $table = $table . "<tr" . $rowClass . "><td>" . $value["BillRefNo"] . "</td><td>" . $value["RN"] . "</td>"
                        . "<td>" . $value["CompanyName"] . "</td><td>" . $value["FirstName"] . " " . $value["LastName"] . " " . $value["Natregno"] . "</td>"
                        . "<td>" . $value["HoursWorked"] . "</td><td>" . $value["TotalPaymentAmount"] . "</td>"
                        . $buttonCode . $AmendButton . "</tr>";
            }
        }
        $template = new BaseTemplate();
        $template->load("Views/Payment/table.html");
        $template->replace("unlock", $unlock);
        $template->replace("table", $table);
        $template->publish();
    }

}

class PaymentUnlockController extends MakePaymentController {

    function __construct() {
        $this->setRoles(['Administrator', 'Manager', 'Super User']);
    }

    function show($params) {
        $pymtinst = new \BarcomModel\Payment();
        $rn = $_REQUEST['id'];
        $unlocked = $pymtinst->UnlockPayment($rn);
        if ($unlocked) {
            $message = "Unlock Successful";
        } else {
            $message = "Unlock failed alert the developers";
        }

        $template = new BaseTemplate();
        $template->load("Views/Payment/unlocksuccess.html");
        $template->replace("paymentname", $rn);
        $template->replace("message", $message);
        $template->publish();
    }

}

?>
