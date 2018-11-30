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

    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        echo ("<div class=\"alert alert-danger\" role=\"alert\">You must be logged in to view this page.</div>");
    }
    else {
        //TODO: Check if user is in a squad
        $handler =  $db = new Database();
        $handler = $db->connect();

        //all possible errors here (player has no squad, database problem, etc.) are handled with throws
        try {
            $squad = Squad::load($handler, $_SESSION['user']);
            //Display squad info.
            //TODO: Squad image
            echo("
                <h1>Squad Overview</h1><br>
                <table class='table table-dark'>
                    <thead class='thead-dark'>
                    <tr>
                        <th scope='col'>".$squad->getName()."</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <th scope='row' style='width: 25%'>Leader</th>
                    <td>".$squad->getLeader()."</td>
                    </tr>
                    <tr>
                    <th scope='row' style='width: 25%'>Players</th>
                    <td>".$squad->getPlayersPrettyString()."</td>
                    </tr>
                    <th scope='row' style='width: 25%'>URL</th>
                    <td><a href='https://".$squad->getUrl()."'>".$squad->getUrl()."</a></td>
                    </tr>
                    <th scope='row' style='width: 25%'>Side</th>
                    <td>".$squad->getSide()."</td>
                    </tr>
                    <th scope='row' style='width: 25%'>Credits</th>
                    <td>".$squad->getCredits()."</td>
                    </tr>
                    </tr>
                    <th scope='row' style='width: 25%'>Status</th>
                    <td>".$squad->getStatus()."</td>
                    </tr>
                    <tbody>
                </table>
            ");
            
            //Alert user if status is pending:
            if ($squad->getStatus() == "pending") {
                echo("
                    <div class=\"alert alert-danger\" role=\"alert\">
                        Your squad is not validatet yet! You can not take any squad actions. Please contact an
                        admin. Together with you and other players, the admin will decide on which side you play
                        and then validate your squad.
                    </div>
                ");
            }

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