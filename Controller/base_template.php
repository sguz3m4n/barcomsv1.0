<?php

namespace Controllers;

require __DIR__ . "/../dbconfig.php";

class BaseTemplate {

    public $template;

    function load($templatepath) {
        $this->template = file_get_contents($templatepath);
    }

    function replace($var, $content) {
        $this->template = str_replace("{ $var }", $content, $this->template);
    }

    /* function publish() {
      $this->template = str_replace("{ theme }", $_SESSION['theme'], $this->template);
      eval("?>".$this->template."<?");
      } */

    function publish() {
        $this->template = str_replace("{ theme }", "<link href='assets/global/css/" . $_SESSION['theme'] . "' rel='stylesheet' type='text/css'>", $this->template);
        eval("?>" . $this->template . "<?");
    }

}

class MasterTemplate extends BaseTemplate {
    private $head;
    private $foot;
    private $today;
    
    public $template;
    
    function __construct() {
        $this->today = date("D d M, Y");
        $this->foot = file_get_contents("Views/MasterPage/foot.html");
        $this->head = file_get_contents("Views/MasterPage/head.html");
    }
    
    function publish(){
        //$this->template = str_replace("{ date }",$this->today,$this->base);
        //$this->template = str_replace("{ name }",$_SESSION['firstname'],$this->base);
        $this->template = str_replace("{ head }",$this->head,$this->template);
        $this->template = str_replace("{ foot }",$this->foot,$this->template);
        eval("?>".$this->template."<?");
    }
}

abstract class BaseController {

    abstract public function show($params);

    function display($params = null) {
        $this->show($params);
    }

}

abstract class LoggedInController extends BaseController {

    function display($params = null) {
        $this->login();
        $this->show($params);
    }

    function login() {
        if (!isset($_SESSION['logged_in'])) {
            header("Location: /login");
        }
    }

}

abstract class PermissionController extends LoggedInController {

    private $roles = NULL;

    function display($params = null) {
        $this->login();
        $this->permissions();
        $this->show($params);
    }

    function permissions() {
        if (isset($this->roles) && isset($_SESSION['user_roles'])) {
            $userroles = $this->getRoles();
            $permissions = $_SESSION['user_roles'];
            if (!in_array($permissions, $userroles)) {
                header("Location: /login");
            }
        }
    }

    public function setRoles($roles) {
        $this->roles = $roles;
    }

    public function getRoles() {
        return $this->roles;
    }

}
