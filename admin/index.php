<?php
    ini_set("display_errors", 1);

    session_start();
    session_regenerate_id();

    $error = $message = $name = $password = "";

    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/headObject.php");
    require_once($basePath."includes/database.php");

    //check if we have post-data
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        //because we include content by "GET", we can get different kind of post-requests here
        // => check for admin login first
        if (isset($_POST['login'])) {
            $result = verifyLogin($_POST, $basePath);
            $error .= $result['error'];
            $message = $result['message'];
        }
    }

    //activate database connection
    $db = new Database();
    $connection = $db->connect();

    //display menu
    $head = new Head($pageTitle, $basePath);
    if (isset($_SESSION['admin'])) {
        $head->addMenuItem(true, "Squad managment", "squadOverview.php");
        $head->addMenuItem(true, "Inventory managment", "addInventory.php"); 
        $head->addMenuItem(true, "Inventory overwiev", "index.php?content=viewInventory");
        $head->addMenuItem(false, "Logout", "logout.php");
    }
    $head->display();

    //display errors or messages
    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }

    //if user is not logged in, display login form:
    if (!(isset($_SESSION['admin']))) {
        include("content/loginForm.html");
    }
    //check for post parameters only a logged in admin could have submitted:
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['updateItem'])) {
            include("viewInventory.php");
        }
    }
    //user is logged in -> check for get-parameters
    else if (isset($_GET['content'])) {
        //TODO IMPORTANT: Make a list of allowed content and check !!!
        include($_GET['content'].".php");
    }
    include($basePath."includes/foot.php");
?>


<?php 
    //helper function: verify admin login. Takes $_POST-array as input. Sets a session for the user
    //if succesfull, sets an error otherwise
    function verifyLogin($array, $basePath) {
        $error = $message = "";
        try {
            require_once($basePath."validations/name.php");
            if (isset($array['username'])) {
                $name = new Name($array['username']);
            }
            else {
                $error .= "Enter your Playername.";
            }
            if (isset($array['password'])) {
                $password = $array['password'];
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
        return array(
            'message' => $message,
            'error' => $error
        );
    }

?>