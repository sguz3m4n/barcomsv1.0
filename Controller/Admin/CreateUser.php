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

class CreateAdminUserController extends PermissionController {

    private $result;

    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_POST['btn-create'])) {
            $usrinst = new \BarcomModel\UserModule();
            $Natregno = $usrinst->Natregno = $_POST['Natregno'];
            $username = $usrinst->UserName = $_POST['UserName'];
            $usrinst->Password = $_POST['Password'];
            $firstname = $usrinst->FirstName = $_POST['FirstName'];
            $role = $usrinst->Roles = $_POST['Roles'];
            if (!$usrinst->IfExists($Natregno)) {
                $usrinst->CreateNewUser();
                $token = '<br><br><span class="label label-success">User Name</span> ' . '<span class="label label-info"> ' . $username . '</span><br><br><br>' .
                        '<span class="label label-success">Display Name</span> ' . '<span class="label label-info">' . $firstname . '</span><br><br><br>' .
                        '<span class="label label-success">Role</span> ' . '<span class="label label-info">' . $role . '</span><br><br><br>' .
                        '<span class="label label-success">National Registration Number</span> ' . '<span class="label label-info">' . $Natregno . '</span><br>';

                $token1 = 'Record Successfully Created';
                header("Location:" . "/success?result=$token&header=$token1&args=");
            } else {
                foreach ($usrinst->SystemRoles as $value) {
                    $test = $value;
                    $this->result .= "<option id='$test' >" . $value . "</option>";
                }
                $roles = $this->result;
                $template = new MasterTemplate();
                $template->load("Views/Admin/manage.html");
                $template->replace("roles", $roles);
                $template->replace("UserName", $username);
                $template->replace("FirstName", $firstname);
                $template->replace("Natregno", $Natregno);
                $template->replace("error", "Sorry this user already exists");
                $template->publish();
            }
        } else
        if (isset($_GET)) {
            $model = new \BarcomModel\UserModule();

            foreach ($model->SystemRoles as $value) {
                $test = $value;
                $this->result .= "<option id='$test' >" . $value . "</option>";
            }
            $roles = $this->result;
            $template = new MasterTemplate();
            $template->load("Views/Admin/manage.html");
            $template->replace("roles", $roles);
            $template->replace("error", "");
            $template->replace("UserName", "");
            $template->replace("FirstName", "");
            $template->replace("Natregno", "");
            $template->publish();
        }
    }

}

?>
