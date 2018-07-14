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

class ManageTravelController extends PermissionController {

    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create'])) {
            $trvinst = new \BarcomModel\ManageTravel();
            $code = $trvinst->TravCode = $_POST['TravelCode'];
            $rate = $trvinst->TravRate = $_POST['TravelRate'];
            $trvinst->ModifiedBy = $username;

            if ($trvinst->CreateTravelRate()) {
                $token = '<br><br><span class="label label-success">Travel Code</span> ' . '<span class="label label-info"> ' . $code . '</span><br><br><br>' .
                        '<span class="label label-success">Travel Rate</span> ' . '<span class="label label-info">' .'$'. number_format($rate, 2, '.', '') . '</span><br>';

                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                echo 'Something went wrong. Please try again';
            }
        } else
        if (isset($_POST['btn-update'])) {
            $trvinst = new \BarcomModel\ManageTravel();
            $code = $trvinst->TravCode = $_POST['TravelCode'];
            if ($trvinst->IfExists($trvinst->TravCode)) {
                $rate = $trvinst->TravRate = $_POST['TravelRate'];
                $trvinst->ModifiedBy = $username;
                $trvinst->UpdateTravelRate($trvinst->TravCode);
                $token = '<br><br><span class="label label-success">Travel Code</span> ' . '<span class="label label-info"> ' . $code . '</span><br><br><br>' .
                        '<span class="label label-success">Travel Rate</span> ' . '<span class="label label-info">' .'$'. number_format($rate, 2, '.', '') . '</span><br>';
                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                echo 'User does not currently exist. Please create new user';
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\ManageTravel();
            $travelcode = $model->GetTravelCodes();
            $template = new MasterTemplate();
            $template->load("Views/Admin/travel.html");
            $template->replace("travelcode", $travelcode);
            $template->publish();
        }
    }

}

?>