<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Payroll.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

class PayrollController extends PermissionController {

    function __construct() {
        $this->setRoles(['Manager', 'Super User']);
    }

    private $National_Id;

    function show($params) {
        $payrollinst = new \BarcomModel\Payroll();
        $User = $payrollinst->User = $_SESSION["login_user"];
        $this->National_Id = $_SESSION['NatReg'];

        if (isset($_POST['btn-process'])) {
            $payrollinst->BatchDate = $_POST["BatchDate"];
            $payrollinst->BatchId = $_POST['BatchId'];
            $payrollinst->ProcessPayments();
            if ($payrollinst->auditok == 1) {

                $audinst = new \BarcomModel\Audit();

                $tranid = $audinst->TranId = $audinst->GenerateTimestamp('PYRL');
                $AudtDesc = 'Payroll for ' . $payrollinst->BatchId;
                $TranDesc = 'Payroll process ' . $payrollinst->BatchId;
                $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                //$audinst->CreateTransAuditRecord($tranid, $AudtDesc, $User, "PMT", "", "");

                $token = '<br><br><span class="label label-success">Batch Id</span> ' . '<span class="label label-info"> ' . $payrollinst->BatchId . '</span><br><br><br>' .
                        '<span class="label label-success">Batch Date</span> ' . '<span class="label label-info"> ' . $payrollinst->BatchDate . '</span><br><br><br>' .
                        '<span class="label label-success">Status</span> ' . '<span class="label label-info"> ' . 'Processed' . '</span><br><br><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            }
        } else {
            $template = new MasterTemplate();

            $BatchId = $payrollinst->GenerateTimestamp("BTCH");
            $template->load("Views/Payroll/processpayroll.html");
            $template->replace('BatchId', $BatchId);
            $template->publish();
        }
    }

}

?>
