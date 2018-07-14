<?php

namespace Controllers;

require 'Controller/base_template.php';
require 'Classes/Audit.php';

class PasswordController extends PermissionController {

    function show($params) {
        $error = NULL;
        $audinst = new \BarcomModel\Audit();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $conn = conn();
            $password = $_POST['password'];
            $password_copy = $_POST['password_copy'];
            $username = $_SESSION['login_user'];

            if ($password == $password_copy) {
                $hash_password = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET password='$hash_password' WHERE username='$username'";
                //$result = $conn->query("UPDATE users SET password='$hash_password' WHERE username='$username'");

                if ($conn->exec($sql)) {
                    // Redirect                    
                    $tranid = $audinst->TranId = $audinst->GenerateTimestamp('CPWD');
                    $TranDesc = 'Change password for ' . $username;
                    $User = $username;
                    $audinst->CreateUserAuditRecord($tranid, $User, $TranDesc);
                    $token = '<br><br><span class="label label-success">Employee Name</span> ' . '<span class="label label-info"> ' . $username . '</span><br><br><br>';
                    $token1 = 'Password successfully changed';
                    header("Location:" . "/success?result=$token&header=$token1&args=");
                }
                $conn = NULL;
            } else {
                $error = "Entered password in both fields must match to change password";
            }
        } else {
            //$template = new MasterTemplate();
            //$template->load(__DIR__ . "/../Views/password.html");
            $template = new MasterTemplate();
            $template->load("Views/password.html");
            $template->replace("error", $error);
            $template->publish();
        }
    }

}
