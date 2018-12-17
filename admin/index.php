<?php

    /* 
    admins main page 
    -   regenerate id. There will never be hundreds of admin, so we do not 
        care about the traffic for regenerate here
    */
    ini_set("display_errors", 1);

    session_start();
    session_regenerate_id(); 
    

    //setup variables. AllowedContent: What sites the user can access via GET-Request
    $error = $message = $name = $password = "";
    $allowedContent = [ "viewInventory", "squadOverview", "addInventory" ];

    $pageTitle = "Admin Panel";
    $basePath = "../";
    
    require_once($basePath."includes/headObject.php");
    require_once($basePath."includes/database.php");
    require_once($basePath."validations/itemClass.php");
    require_once($basePath."validations/side.php");
    require_once($basePath."validations/itemType.php");

    //check if we have post-data, and if so, verify admins login data
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST['login'])) {
            $result = verifyLogin($_POST, $basePath);
            $error .= $result['error'];
            $message = $result['message'];
        }
    }

    //activate database connection
    $db = new Database();
    $connection = $db->connect();

    //display header menu
    $head = new Head($pageTitle, $basePath);
    if (isset($_SESSION['admin'])) {
        $head->addMenuItem(true, "Squad managment", "squadOverview.php");
        $head->addMenuItem(true, "Inventory managment", "index.php?content=addInventory"); 
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

    //CONTENT-STEERING:
    //1. if user is not logged in, display login form:
    if (!(isset($_SESSION['admin']))) {
        include("content/loginForm.html");
    }
    //2. else, check for post parameters:
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['updateItem'])) {
            include("viewInventory.php");
        }
        else if (isset($_POST['addItem'])) {
            include("addInventory.php");
        }
    }
    //3. check for get parameters and include content accordingly:
    else if (isset($_GET['content'])) {
        if (in_array($_GET['content'], $allowedContent)) {
            include($_GET['content'].".php");
        }
    }

    //display footer content:
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