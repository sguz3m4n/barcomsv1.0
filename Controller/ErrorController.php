<?php
namespace Controllers;
require __DIR__."/base_template.php";


class Error extends BaseController {
    function show($vars) {
        $template = new BaseTemplate();
        $template->load("Views/error.html");
        $template->replace("message", $vars);
        $template->publish();
    }
}
