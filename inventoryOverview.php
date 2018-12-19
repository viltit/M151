<?php

    ini_set("display_errors", 1);
    $pageTitle = "My Squad";
    $message = $error = "";

    include("includes/head.php");

    require_once("includes/database.php");
    require_once("validations/squad.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    //this is a sensible area (do NOT let the enemy see your inventory). So we regenerate the session:
    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        echo ("<div class=\"alert alert-danger\" role=\"alert\">You must be logged in to view this page.</div>");
    }
    else {
        require_once("validations/inventory.php");
        require_once("validations/user.php");

        $db = new Database();
        $connection = $db->connect();

        //TODO: Identical database-access like in squadOverwiew -> set a session variable ?
        $squad = Squad::load($connection, $_SESSION['user']);
        $inventory = Inventory::create($connection, $squad->getName());

    }

    include("includes/foot.php");

?>