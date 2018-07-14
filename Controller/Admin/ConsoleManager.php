<?php
/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */
namespace Controllers;

require 'Controller/base_template.php';

class ConsoleController extends PermissionController {

    private $result;

    function show($params) {
        $username = $_SESSION["login_user"];

        if (isset($_GET)) {
       
            $roles = $this->result;
            $template = new MasterTemplate();
            $template->load("Views/Admin/console.html");
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
