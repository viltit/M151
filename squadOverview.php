<?php
    ini_set("display_errors", 1);
    $pageTitle = "My Squad";
    $message = $error = $squad = "";

    include("includes/head.php");
    require_once("includes/database.php");
    require_once("validations/squad.php");
    require_once("includes/squadStatus.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }

    //TODO: Check if user is in a squad
    $handler =  $db = new Database();
    $handler = $db->connect();

    //check the squads status
    $status = getSquadStatus($handler);
    $squad = "";
    if (isset($status['error'])) {
        $error .= $status['error'];
    }
    else {
        $squad = $status['squad'];
    }
    if (isset($status['message'])) {
        $message .=  $status['message'];
    }
    //user is not squad leader? Let him see the page, but he can not perform any action
    if(empty($error) && !isset($_SESSION['squadLeader'])) {
        $message .= "You are not the leader of your squad. You can view this page, but you are not allowed
                to buy or sell any items.";
        $isLeader = false;
    }
    else {
        $isLeader = true;
    }

    //display errors or messages
    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }

    if (empty($error)) {
        //TODO: LOOK AT AUTO-LOADING CLASSES. RIGHT NOW WE NEED TO RELOAD THE SQUAD FROM DATABASE EVERY TIME
        //if (!isset($_SESSION['squad'])) {
            try {
                $squad = Squad::load($handler, $_SESSION['user']);
                //$_SESSION['squad'] = $squad;
            }
            catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
            catch (NoSquadException $e) {
                $error = "
                    You are not part of a squad yet! <br>
                    <ul>
                    <li>If you have at least three players ready, you can apply for your own squad
                    <a href='squadRegister.php'><button type='button' class='btn btn-primary btn-sm'>here</button></a></li>
                    <li>If you are part of an existing squad, your squad leader can register you.</li>
                    <li>If you are alone and want to be part of a squad, please contanct an admin.</li>
                    </ul>";
            }
        //}
        //else {
        //    $squad = $_SESSION['squad'];
        //}
      
        if (empty($error)) {
            //Display squad info.
            //TODO: Squad image
            echo("
                <h1>Squad Overview</h1><br>
                <table class='table'>
                    <thead class='thead'>
                    <tr>
                        <th scope='col'>Name</th>
                        <th scope='col'>".$squad->getName()."</th>
                    </tr>
                    <tr>
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
        }
    }

    include("includes/foot.php");

?>