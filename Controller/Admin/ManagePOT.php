<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

require 'Classes/Admin.php';
require 'Classes/Audit.php';
require 'Controller/base_template.php';

class ManagePOTController extends PermissionController {

    function show($params) {
        $username = $_SESSION["login_user"];
        if (isset($_POST['btn-create'])) {
            $potins = new \BarcomModel\ManagePOT();
            $role = $potins->RoleCode = $_POST['RoleCode'];
            $rolename = $potins->RoleName = $_POST['RoleName'];
            $code = $potins->RateCode = $_POST['RateCode'];
            $rate = $potins->RateAmount = $_POST['RateAmount'];
            $potins->ModifiedBy = $username;

            if ($potins->CreateNewPOT()) {

                $token = '<br><br><span class="label label-success">Overtime Role Code</span> ' . '<span class="label label-info"> ' . $role . '</span><br><br><br>' .
                        '<span class="label label-success">Overtime Role Name</span> ' . '<span class="label label-info"> ' . $rolename . '</span><br><br><br>' .
                        '<span class="label label-success">Overtime Payment Code</span> ' . '<span class="label label-info"> ' . $code . '</span><br><br><br>' .
                        '<span class="label label-success">Overtime Payment  Rate</span> ' . '<span class="label label-info">' .'$'. number_format($rate, 2, '.', '') . '</span><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                echo 'Something went wrong. Please try again';
            }
        } else
        if (isset($_POST['btn-update'])) {
            $potins = new \BarcomModel\ManagePOT();
            $code = $potins->RateCode = $_POST['RateCode'];
            if ($potins->IfExists($potins->RateCode)) {
                $rate = $potins->RateAmount = $_POST['RateAmount'];
                $potins->ModifiedBy = $username;
                $potins->UpdateOT($potins->RateCode);
                $token = '<br><br><span class="label label-success">Overtime Payment Code</span> ' . '<span class="label label-info"> ' . $code . '</span><br><br><br>' .
                        '<span class="label label-success">Overtime Payment Rate</span> ' . '<span class="label label-info">'.'$' . number_format($rate, 2, '.', '') . '</span><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                echo 'User does not currently exist. Please create new user';
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\ManagePOT();
            $ratecode = $model->GetRateCode();
            $template = new MasterTemplate();
            $template->load("Views/Admin/overtime.html");
            $template->replace("ratecode", $ratecode);
            $template->publish();
        }
    }

}
