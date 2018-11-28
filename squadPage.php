<?php
    ini_set("display_errors", 1);
    $pageTitle = "My Squad";

    include("includes/head.php");

    require_once("includes/database.php");
    require_once("validations/squad.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        echo ("<div class=\"alert alert-danger\" role=\"alert\">You must be logged in to view this page.</div>");
    }
    else {
        //TODO: Check if user is in a squad
        $handler =  $db = new Database();
        $handler = $db->connect();
        try {
            $squad = Squad::load($handler, $_SESSION['user']);
            //TODO: Display squad info
            //TODO: Menu item for inventory inspection
        }
        catch (InvalidArgumentException $e) {
            echo ("<div class=\"alert alert-danger\" role=\"alert\">".$e->getMessage()."</div>");
        }
        catch (NoSquadException $e) {
            echo("<div class=\"alert alert-danger\" role=\"alert\">
                You are not part of a squad yet! <br>
                <ul>
                <li>If you have at least three players ready, you can apply for your own squad
                <a href='squadRegister.php'><button type='button' class='btn btn-primary btn-sm'>here</button></a></li>
                <li>If you are part of an existing squad, your squad leader can register you.</li>
                <li>If you are alone and want to be part of a squad, please contanct an admin.</li>
                </ul>
            </div>");
        }
    }

    include("includes/foot.php");

?>