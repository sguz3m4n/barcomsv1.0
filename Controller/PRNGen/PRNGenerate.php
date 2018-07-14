<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

require 'Classes/PreReqNum.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';
static $stprnlist;

class DisburseController extends PermissionController {

    private $prnslist;

    function __construct() {
        $this->setRoles(['Payment Clerk', 'Manager', 'Super User']);
    }

    function show($params) {

        $username = $_SESSION["login_user"];

        $prninst = new \BarcomModel\PreReqNum();
        $audinst = new \BarcomModel\Audit();


        if (isset($_POST['btn-create'])) {
            $User = $username;
            $varid = $prninst->CompanyId = $_POST['CompId'];
            $RequestName = $prninst->RequestorName = $_POST['EmpName'];
            $RequestCount = $prninst->RequestCount = $_POST['PRNCount'];
            $companyname = $_POST['CompName'];
            $prninst->RequestorId = $_POST['Natregno'];
            $tranid = $prninst->GenerateTimestamp();

            for ($i = 0; $i < $RequestCount; $i++) {
                $prn = new \BarcomModel\PreReqNum();
                $prnid = $_SESSION['prnlist'][$i];
                $prn->CreatePRNManager($tranid, $prnid, $User);
                $this->prnslist = $this->prnslist . '<br><span class="label label-info"> ' . $prnid . '</span><br>';
            }

            if ($prn->prnmanok == 1) {
                $prninst->CreatePRNTransaction($tranid, NULL, $User);
                if ($prninst->auditok == 1) {
                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('PRN');
                    $AudtDesc = 'Generate ' . $prninst->RequestCount . ' PRN(s) for  ' . $varid;
                    $TranDesc = 'Generate ' . $prninst->RequestCount . " PRN(s) for Requestor " . $RequestName;
                    $audinst->CreateUserAuditRecord($tranid, $User, $AudtDesc);
                    $audinst->CreateTransAuditRecord($tranid, $AudtDesc, $User, "PRN", $RequestCount, $varid);

                    $token = '<br><br><span class="label label-success">Company Id</span> ' . '<span class="label label-info"> ' . $varid . '</span><br><br><br>' .
                            '<span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $companyname . '</span><br><br><br>' .
                            '<span class="label label-success">You requested</span> ' . '<span class="label label-info">' . $RequestCount . ' PRNs' . '</span><br>' . $this->prnslist;

                    $token1 = 'Record Successfully Created';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            }
        } else
        if (isset($_GET)) {
            $template = new MasterTemplate();
            $template->load("Views/PRNGen/disburse.html");
            $template->replace("title", "Create New Bill Payment");
            $template->publish();
        }
    }

}

class EditPRNController extends DisburseController {

    function __construct() {
        $this->setRoles(['Payment Clerk','Manager', 'Super User']);
    }
   
    function show($params) {
        $prninst = new \BarcomModel\PreReqNum();
        $audinst = new \BarcomModel\Audit();

        if (isset($_POST['submit'])) {
            $User =  $_SESSION["login_user"];
            $varid = $prninst->CompanyId = $_POST['CompId'];
            $OldCompId = $_POST['OldCompId'];
            $RequestName = $prninst->RequestorName = $_POST['EmpName'];
            $RequestCount = $_POST['RequestCount'];
            $prninst->RequestCount = 1;
            $companyname = $_POST['CompName'];
            $prninst->RequestorId = $_POST['RequestorId'];
            $tranid = $_POST['TranId'];
            $newtranid = $audinst->TranId = $audinst->GenerateTimestamp('PRN');
            $prnid = $_POST['PRNumber'];
            if($RequestCount>1){
                // Update prntransaction record and decrement count
                // Create new prntraction record with count of 1
                // Update prnmanage record with details
                $prninst->UpdatePRNTransaction($OldCompId,$tranid,$RequestCount-1,$User);
                $prninst->CreatePRNTransaction($newtranid, NULL, $User);            
                $prninst->UpdatePRNManager($newtranid, $prnid, $User);
            }
            else {
                // Update prntransaction record
                $prninst->UpdatePRNTransaction($varid,$tranid,1,$User);
                // Update prnmange record with details
                $prninst->UpdatePRNManager($newtranid, $prnid, $User);
            }
            if ($prninst->auditok == 1) {                              
                $AudtDesc = 'Edit for ' . $varid;
                $TranDesc = 'Edit' . $RequestCount . " PRN Requestor " . $RequestName;
                $CreateDesc = 'Create transaction from edit '.$tranid.'from user'.$varid;
                $audinst->UpdateUserAuditRecord($tranid, $User, $AudtDesc);
                $audinst->UpdateTransAuditRecord($tranid, $AudtDesc, $User, "PRN", $RequestCount, $varid);
                $audinst->CreateUserAuditRecord($newtranid, $varid, $CreateDesc);
            }
            $token = '<br><br><span class="label label-success">You reassigned </span> ' . '<span class="label label-info">' . $prnid .'</span><br>'.
                            '<span class="label label-success">From Company </span> ' . '<span class="label label-info"> ' . $OldCompId . '</span><br>'.
                            '<span class="label label-success">To Company </span> ' . '<span class="label label-info"> ' . $varid . '</span><br><br><br>';

                    $token1 = 'PRN Reassigned Successfully';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
        }
        $template = new MasterTemplate();
        $template->load("Views/PRNGen/edit.html");
        $template->replace("title", "Ammend PRN");
        $template->publish();
    }     

}


class PRNTableController extends DisburseController {
    function show($params) {
       $roles = $_SESSION["user_roles"];
       $canUnlock = in_array($roles,array('Administrator','Manager','Super User'))?true:false;
       $unlock = $canUnlock==1?"<th>Unlock</th>":"";
       $table = "";
       
       $pymtinst = new \BarcomModel\PreReqNum();
       $filterBy = array();
 
        if (isset($_REQUEST['compname'])) {
            $filterBy['companyName'] = "B.CompanyName='".$_REQUEST['compname']."'";
        }

        if (isset($_REQUEST['empid'])) {
            $filterBy['employeeName'] = "A.RequestorName LIKE '".$_REQUEST['empid']."'";
        }

        $results = $pymtinst->GetFilteredPRN($filterBy);
        
        if(count($results) >0) {
            foreach ($results as $value) {
                if ($value["Status"] !='Active') {
                    $rowClass='class="active"';
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' disabled id=".$value["PRNumber"].">Closed</button></td>";
                    if ($canUnlock) {
                        $unlockButton = "<td><span class='glyphicon glyphicon-lock unlock' id=".$value["PRNumber"]."></span></td>";
                        $buttonCode = $buttonCode.$unlockButton;
                    }
                }
                else { 
                    $rowClass = "class='danger'";
                    $buttonCode = "<td><button type='button' class='btn btn-info btn-sm edit' data-toggle='modal' id=".$value["PRNumber"].">Reassign</button></td>";
                    if ($canUnlock) {
                        $unlockButton = "<td></td>";
                        $buttonCode = $buttonCode.$unlockButton;
                    }
                }
                $table = $table."<tr".$rowClass."><td>" . $value["CompanyName"] . "</td>"
                        . "<td>" . $value["RequestorName"] . "</td><td>" . $value["PRNumber"] . "</td>"
                        ."<td>" . $value["Status"] . "</td>"
                        .$buttonCode."</tr>";
            }
        }
        
        else {
            $table = "<tr>No PRNs Found</tr>";
        }

        $template = new BaseTemplate();
        $template->load("Views/PRNGen/table.html");
        $template->replace("unlock", $unlock);
        $template->replace("table", $table);
        $template->publish();
        
    }
}

class PRNUnlockController extends DisburseController {
    function __construct() {
        $this->setRoles(['Administrator', 'Manager', 'Super User']);
    }

    function show($params) {
        $prninst = new \BarcomModel\PreReqNum();
        $rn = $_REQUEST['id'];
        $unlocked = $prninst->ChangePRNStatus($rn);
        if($unlocked) {
            $message = "Unlock Successful";
        }
        else {
            $message = "Unlock failed alert the developers";
        }
        
        $template = new BaseTemplate();
        $template->load("Views/PRNGen/unlocksuccess.html");
        $template->replace("paymentname", $rn);
        $template->replace("message", $message);
        $template->publish();
    }
}
?>
