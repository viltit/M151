<?php

    /*--------------- PREPARE SESSION, REQUIRES ETC. --------------------------------------------- */
    ini_set("display_errors", 1);
    session_start();

    $pageTitle = "Arma3 Real War Liga";
    $basePath = $errors = $messages = "";

    require_once("includes/headObject.php");
    require_once("includes/login.php");
    require_once("includes/database.php");
    require_once("validations/user.php");
    require_once("validations/squad.php");
    require_once("includes/squadStatus.php");
    require_once("validations/inventory.php");

    $loginFormTypes = ["text", "password"];
    $loginFormNames = ["username", "password", "login"];
    $loginFormPlaceholders = ["Username", "Password", "Sign In"];

    $allowedContent = [
        "forum",
        "map",
        "squadOverview",
        "inventoryOverview",
        "marketplace",
        "logout",
        "register",
        "profile",
        "squadRegister"
    ];

    $allowedPost = array(
        "toGame" => "inventoryOverview",
        "toCamp" => "inventoryOverview",
        "buyItem" => "marketplace",
        "sellItem" => "marketplace",
        "updateProfile" => "profile",
        "updatePassword" => "profile",
        "register" => "register",
        "squadRegister" => "squadRegister"
    );

    /* ------------- PREPARE DATABASE CONNECTION ------------------------------------------------- */
    $db = new Database();
    $connection = $db->connect();

    /*--------------  CHECK FOR LOGIN ------------------------------------------------------------ */
    //We will check for POST again later .... :(
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['login'])) {
            try {
                Login::confirm($connection, $_POST['username'], $_POST['password']);
                $messages .= "Hello, ".$_SESSION['user']." You are now logged in.";
            }
            catch (InvalidLoginException $e) {
                $errors .= "Your username or password are not correct. Can not log in.";
            }
        }
    }

    /*--------------- DISPLAY HEAD WITH MENU BAR ------------------------------------------------- */
    $head = new Head($pageTitle, $basePath);
    
    $head->addMenuItem(true, "Forum", "index.php?content=forum");
    $head->addMenuItem(true, "Tactical Map", "index.php?content=map"); 

    if (isset($_SESSION['user'])) {
        $head->addDropdown("MySquad", array(
            "Squad Overview" => "index.php?content=squadOverview",
            "Inventory Overview" => "index.php?content=inventoryOverview",
            "Marketplace" => "index.php?content=marketplace"
        ));
        $head->addMenuItem(false, "Logout", "index.php?content=logout");
        $head->addMenuItem(false, "Profile", "index.php?content=profile");
    }
    else {
        $head->addForm($loginFormTypes, $loginFormNames, $loginFormPlaceholders);
        $head->addMenuItem(false, "<b>Not a user yet? Register here", "index.php?content=register");
    }

    $head->display();

    /*-------------- DISPLAY ERRORS OR MESSAGES --------------------------------------------------- */
    if (!empty($messages)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$messages."</div>";
    }
    if (!empty($errors)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$errors."</div>";
    }

    /*--------------  CONTENT STEERING ------------------------------------------------------------ */
    //Do we have a POST-Request?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach($allowedPost as $name => $url) {
            if (isset($_POST[$name])) {
                include("includes/".$url.".php");
            }
        }
    }

    //If not, do we have a GET-Request?
    else if (isset($_GET['content'])) {
        if (in_array($_GET['content'], $allowedContent)) {
            include("includes/".$_GET['content'].".php");
        }
    }

    include("includes/foot.php")


?>