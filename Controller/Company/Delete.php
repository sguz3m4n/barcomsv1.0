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

class CompanyDeactivateController extends PermissionController {

    function __construct() {
        $this->setRoles(['Manager', 'Administrator', 'Super User']);
    }

    function show($params) {

        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-delete'])) {
            $compinst = new \BarcomModel\Company();
            $audinst = new \BarcomModel\Audit();

            $CompanyId = $_POST['CompanyId'];
            //Check to see if the record already exists            
            //If it does execute delete
            if ($compinst->IfExists($CompanyId) === 0) {
                //Get Id from browser interface
                $varid = $CompanyId;
                $compname = $_POST['CompanyName'];
                $compinst->RecModifiedBy = $username;

                $compinst->DeleteCompany($CompanyId);
                if ($compinst->auditok == 1) {
                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('DCMP');
                    $TranDesc = 'Delete Company for ' . $varid . " Name " . $compname;
                    $User = $username;
                    $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                    $token = '<br><br><span class="label label-success">Company Name</span> ' . '<span class="label label-info"> ' . $compname . '</span><br><br><br>' .
                            '<span class="label label-success">Company Id</span> ' . '<span class="label label-info">' . $varid . '</span><br>';
                    $token1 = 'Record Successfully Deleted';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
            }
        }
    }

}
