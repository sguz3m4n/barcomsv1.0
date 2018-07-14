<?php

/*
  Developed by Kitji Studios
  Development Team: Shayne Marshall, Frederick Masterton Chandler
  Property of Barbados Customs and Excise Department 2017
  Consultation and Analysis by Data Processing Department
  October 2017
 */

namespace Controllers;

class SuccessController extends BaseController {

    function show($params) {
        if (isset($_GET)) {
            $value = $_GET['args'];
            if ($value === "table") {
                $template = new MasterTemplate();
                $template->load("Views/Success/successwithtable.html");
                $template->replace("token1", $_GET['header']);
                $template->replace("token2", $_GET['result']);
                $template->publish();
            } else {
                $template = new MasterTemplate();
                $template->load("Views/Success/success.html");
                $template->replace("token1", $_GET['header']);
                $template->replace("token2", $_GET['result']);
                $template->publish();
            }
        }
    }

}

?>
