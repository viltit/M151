<?php

    ini_set("display_errors", 1);

    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/headObject.php");

    $error = $message = "";

    session_start();

    if (!isset($_SESSION['admin'])) {
        $error .= "This is a restricted site! You must be a logged-in admin to view its content!";
    }
    $head = new Head($pageTitle, $basePath);
    $head->addMenuItem(true, "Squad overview", "squadOverview.php");
    $head->display();

    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
        die();
    }

    //CONTENT FLOW STEERING :
    //check if we have parameters and if so, display the proper menu
    //TODO: DO NOT make a second site for this -> add button to activate in the squad listing
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'activate') {
            //TODO: Load squad from database and add a method "activate"    
        }
        else if ($_GET['action'] == 'delete') {
            //TODO
        }
    }
    else {
        require_once($basePath."validations/squad.php");
        require_once($basePath."includes/database.php");

        //a lot can go wrong while loading all squad.
        try {
            $db = new Database();
            $handler = $db->connect();
            $squads = Squad::loadAll($handler);
        }
        catch (Exception $e) {
            $error .= $e->getMessage();
        }
        
        //TODO: REMOVE THIS, we have all info we need now with Squad::loadAll()
        include("content/squads.php");
    }

    include($basePath."includes/foot.php");

?>