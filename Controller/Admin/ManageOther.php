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

class ManageOtherController extends PermissionController {

    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create-holiday'])) {
            $otherinst = new \BarcomModel\ManageOther();
            $code = $otherinst->HolidCode = $_POST['HolidayCode'];
            $desc = $otherinst->HolidDesc = $_POST['Description'];
            $date = $otherinst->HolidDate = $_POST['HolidayDate'];
            $cal = $otherinst->CalYear = $_POST['CalYear'];
            $otherinst->ModifiedBy = $username;

            if ($otherinst->CreateManageOther()) {
                $token = '<br><span class="label label-success">Holiday Code</span> ' . '<span class="label label-info"> ' . $code . '</span><br><br><br>' .
                        '<span class="label label-success">Holiday Name</span> ' . '<span class="label label-info"> ' . $desc . '</span><br><br><br>' .
                        '<span class="label label-success">Date</span> ' . '<span class="label label-info"> ' . $date . '</span><br><br><br>' .
                        '<span class="label label-success">Caledar Year</span> ' . '<span class="label label-info">' . $cal . '</span><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                echo 'Something went wrong. Please try again';
            }
        } else
        if (isset($_POST['btn-update-holiday'])) {

            $otherinst = new \BarcomModel\ManageOther();
            $pot = new \BarcomModel\ManagePOT();
            $ratecode = $otherinst->RateCode = $_POST['RateCode'];
            $holidcode = $otherinst->HolidCode = $_POST['HolidayCode'];
            $specialrate = $otherinst->SpecialRate = $_POST['SpecialRate'];
            $otherinst->ModifiedBy = $username;

            if (!$otherinst->IfSpecialExists($ratecode, $holidcode)) {
                $otherinst->CreateSpecialRate($ratecode, $holidcode);
                $token = '<br><br><br><br><span class="label label-success">Rate Code</span> ' . '<span class="label label-info"> ' . $ratecode . '</span><br><br><br>' .
                        '<span class="label label-success">Holiday Code</span> ' . '<span class="label label-info"> ' . $holidcode . '</span><br><br><br>' .
                        '<span class="label label-success">Special Rate</span> ' . '<span class="label label-info"> ' . '$' . number_format($specialrate, 2, '.', '') . '</span><br><br><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {


                if ($otherinst->UpdateManageOther($holidcode, $ratecode)) {

                    $token = '<br><br><br><br><span class="label label-success">Rate Code</span> ' . '<span class="label label-info"> ' . $ratecode . '</span><br><br><br>' .
                            '<span class="label label-success">Holiday Code</span> ' . '<span class="label label-info"> ' . $holidcode . '</span><br><br><br>' .
                            '<span class="label label-success">Special Rate</span> ' . '<span class="label label-info"> ' . '$' . number_format($specialrate, 2, '.', '') . '</span><br><br><br>';
                    $token1 = 'Record Successfully Created';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                } else {
                    echo 'Something went wrong. Please try again';
                }
            }
        } else if (isset($_GET)) {
            $model = new \BarcomModel\ManageOther();
            $rates = $model->GetRates();
            $holidaycodes = $model->GetHolidayCode();
            $template = new MasterTemplate();
            $template->load("Views/Admin/other.html");
            $template->replace("rates", $rates);
            $template->replace("holidaycodes", $holidaycodes);
            $template->publish();
        }
    }

}

?>
