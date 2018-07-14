<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

class EditPRNController extends DisburseController {

    function __construct() {
        $this->setRoles(['Manager', 'Super User']);
    }

    function show($params) {
        $prninst = new \BarcomModel\PreReqNum();
        $audinst = new \BarcomModel\Audit();

        if (isset($_POST['submit'])) {
            $User = $_SESSION["login_user"];
            $varid = $prninst->CompanyId = $_POST['CompId'];
            $RequestName = $prninst->RequestorName = $_POST['EmpName'];
            $RequestCount = $_POST['RequestCount'];
            $UpdateCount = $_POST['RequestCount'] - 1;
            $prninst->RequestCount = 1;
            $companyname = $_POST['CompName'];
            $prninst->RequestorId = $_POST['RequestorId'];
            $tranid = $_POST['TranId'];
            $newtranid = $audinst->TranId = $audinst->GenerateTimestamp('PRN');
            $prnid = $_POST['PRNumber'];
            //$prn = new \BarcomModel\PreReqNum();
            //$prnid = $_SESSION['prnlist'][$i];
            $prninst->UpdatePRNTransaction($tranid, $UpdateCount, $User);
            $prninst->CreatePRNTransaction($newtranid, NULL, $User);
            $prninst->UpdatePRNManager($newtranid, $prnid, $User);
            if ($prninst->auditok == 1) {
                $AudtDesc = 'Update for ' . $varid;
                $TranDesc = 'Generate' . $RequestCount . " PRN Requestor " . $RequestName;
                //$audinst->UpdateUserAuditRecord($tranid, $User, $AudtDesc);
                //$audinst->UpdateTransAuditRecord($tranid, $AudtDesc, $User, "PRN", $RequestCount, $varid);
                //$audinst->TransAuditRecord($tranid, $AudtDesc, $User, "PRN", $RequestCount, $varid);
            }
        }
        $template = new MasterTemplate();
        $template->load("Views/PRNGen/edit.html");
        $template->replace("title", "Create New Bill Payment");
        $template->publish();
    }

}

?>
