<?php

namespace Controllers;

require __DIR__ . "/base_template.php";

use Connection;

class Login extends BaseController {

    function show($params) {
        $error = NULL;
        $logo = "BIGlogo.png";
        if ($_SESSION['theme'] != "base.css") {
            $logo = substr($_SESSION['theme'], 0, -3) . "jpg";
        }
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $conn = conn();
            // username and password sent from form
            $username = $_POST['username'];
            $password = $_POST['password'];
            try {
                $result = $conn->prepare("SELECT roles,password,national_id,firstname,lastlogon FROM users WHERE username = :usernamev");
                $result->bindValue(':usernamev', $username, \PDO::PARAM_STR);
                $result->execute();
                $result_array = $result->fetchAll();
                // If user not found error and display login page again
                $result_array = array_filter($result_array);
                if (empty($result_array)) {
                    $error = " User name or password is incorrect";
                } else {
                    $result_array = $result_array[0];
                    $hash_password = $result_array['password'];
                    $roles = $result_array['roles'];
                    $NatReg = $result_array['national_id'];
                    $firstname = $result_array['firstname'];
                    $lastlogin = $result_array['lastlogon'];
                    $password_correct = password_verify($password, $hash_password);
                    if ($password_correct) {
                        $_SESSION['login_user'] = $username;
                        $_SESSION['user_roles'] = $roles;
                        $_SESSION['logged_in'] = TRUE;
                        $_SESSION['NatReg'] = $NatReg;
                        $_SESSION['firstname'] = $firstname;
                        $_SESSION['lastlogon'] = $lastlogin;
                        // REDIRECT TO PAGE USER DESIRED AFTER LOGIN
                        if (isset($_SESSION['REQUEST_URI'])) {
                            $destination = $_SESSION['REQUEST_URI'];

                            header('Location:' . $destination);
                        }
                        // USER WENT TO LOGIN DIRECTLY, SEND THEM TO THE INDEX
                        else {
                            header('Location: \\');
                        }
                    } else {
                        $error = "Your Login Name or Password is invalid";
                    }
                }
            } catch (Exception $ex) {
                $error = $ex;
            }
        }

        $template = new BaseTemplate();
        $relpath = __DIR__ . "/../Views/login.html";
        $template->load($relpath);
        $template->replace("title", "Barbados Customs Login Page");
        $template->replace("date", date("m/d/y"));
        $template->replace("error", $error);
        $template->replace("logo", $logo);
        $template->publish();
    }

    function logout() {
        if (isset($_SESSION['logged_in'])) {
            $_SESSION['logged_in'] = FALSE;
            $_SESSION['login_user'] = NULL;
            session_destroy();
            $error = 'User logged out';
        } else {
            $error = NULL;
        }
        header('Location: \\');
    }

}
