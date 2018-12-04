<?php
    ini_set("display_errors", 1);

    session_start();
    session_regenerate_id();

    $error = $message = $name = $password = "";

    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/headObject.php");
    require_once($basePath."includes/database.php");

    $head = new Head($pageTitle, $basePath);
    if (isset($_SESSION['admin'])) {
        $head->addMenuItem(true, "Pending squads", "squadValidate.php");
    }

    $head->display();

    //check admin login
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            require_once($basePath."validations/name.php");
            if (isset($_POST['username'])) {
                $name = new Name($_POST['username']);
            }
            else {
                $error .= "Enter your Playername.";
            }
            if (isset($_POST['password'])) {
                $password = $_POST['password'];
            }
            else {
                $error .= "Enter your password";
            }
        }
        catch (InvalidArgumentException $e) {
            $error .= $e->getMessage();
        }
        if (empty($error)) {
            //verify login:
            $query = "SELECT Admin.Password FROM Admin INNER JOIN Player ON Admin.Player_id = Player.id WHERE Player.username = :username";
            $db = new Database();
            $handler = $db->connect();
            $stm = $handler->prepare($query);
            $stm->bindParam(":username", $name);
            if (!$stm->execute()) {
                $error .= "We seem to have a problem with our database. Try again later.";
            }
            else {
                if ($stm->rowCount() == 0) {
                    $error .= "You entered an invalid username and / or password.";
                }
                else {
                    $result = $stm->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $result['Password'])) {
                        $_SESSION['admin'] = $name;
                        $message .= "You are now logged in.";
                    }
                    else {
                        $error .= "You entered an invalid username and / or password.";
                    }
                }
            }
        }
    }

    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }


    //TODO: Put content outside of these files
    if (!(isset($_SESSION['admin']))) {
        include("content/loginForm.html");
    }

    include($basePath."includes/foot.php");
?>