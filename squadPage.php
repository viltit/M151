<?php
    ini_set("display_errors", 1);
    $pageTitle = "My Squad";
    include("includes/head.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        echo ("<div class=\"alert alert-success\" role=\"alert\">You must be logged in to view this page.</div>");
    }
    //TODO: Check if user is in a squad and if he is the leader
    
    
    include("includes/foot.php");

?>