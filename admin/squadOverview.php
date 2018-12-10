<?php

    //there is a lot of preparation needed here: includes, session, database connection, ...
    ini_set("display_errors", 1);
    session_start();

    $pageTitle = "Admin Panel";
    $basePath = "../";
    
    require_once($basePath."includes/headObject.php");
    require_once($basePath."validations/side.php");
    require_once($basePath."validations/squad.php");
    require_once($basePath."includes/database.php");

    $db = new Database();
    $handler = $db->connect();

    $error = $message = "";

    //only let admins in:
    if (!isset($_SESSION['admin'])) {
        header("location:index.php");
    }

    //display menu:
    $head = new Head($pageTitle, $basePath);
    $head->addMenuItem(true, "Squad overview", "squadOverview.php");
    $head->display();

    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
        die();
    }

    //check if we have GET-parameters and if so, display the proper menu
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'Activate') {
            if (isset($_GET['name']) && isset($_GET['side'])) {
                try {
                    echo("<h1>Activate</h1>");
                    Squad::activate($handler, $_GET['name'], $_GET['side']);
                    $message .= "Squad ".$_GET['name']." is now active on side ".$_GET['side'];
                }
                catch (InvalidArgumentException $e) {
                    echo("<h1>Error ".$e->getMessage()."</h1>");
                    $error .= $e->getMessage();
                }
            }
        }
        else if ($_GET['action'] == 'delete') {
            //TODO: Should we ask the admin if he really want to take this action?
        }
    }
    //no GET-Params -> just dispay all squads 
    else {
        //a lot can go wrong while loading all squad.
        try {
            $squads = Squad::loadAll($handler);
            displaySquads($squads, $handler);

        }
        catch (Exception $e) {
            $error .= $e->getMessage();
        }
    }

    include($basePath."includes/foot.php");

?>

<?php
    function displaySquads($squads, $connection) {
        echo("<table class='table'>
        <thead>
        <tr>
            <th scope='col'>Name</th>
            <th scope='col'>URL</th>
            <th scope='col'>Image</th>
            <th scope='col'>Leader</th>
            <th scope='col'>Players</th>
            <th scope='col'>Side</th>
            <th scope='col'>Status</th>
        </tr>
        </thead>");

        $allSides = Side::loadAll($connection);

        foreach($squads as $squad) {
            echo("<tr>");
            echo("<td>".$squad->getName()."</td>");
            echo("<td>".$squad->getURL()."</td>");
            //todo: Image
            echo("<td></td>");
            echo("<td>".$squad->getLeader()."</td>");
            //TODO: For many players -> new Lines
            echo("<td>".$squad->getPlayersPrettyString()."</td>"); 
            //TODO: Print side name, not id
            //if squad is not activated, display a drop-down menu with a side to choose
            if ($squad->getStatus() == 'pending') {
                echo("<td><select class='form-control form-control-sm' name='side' form='activate".$squad->getName()."'>");
                foreach($allSides as $side) {
                    echo("<option value='".$side->getName()."'>".$side->getName()."</option>");
                }
                echo("</select></td>");
            }
            else {
                echo("<td>".$squad->getSide()."</td>");
            }
            //status: Mark red when the squad is pending. Add button for activation menu
            $style = "";
            $menu = "";
            if ($squad->getStatus() == 'pending') {
                $style = " class='table-danger'";
                $menu = "<br><form class='form-group' name='activate' method='GET' id='activate".$squad->getName()."'>
                        <input type='hidden' name='name' value='".$squad->getName()."'>
                        <input type='submit' name='action' value='Activate'></form>";
            }
            else if ($squad->getStatus() == 'inactive') {
                $style = " class='table-warning'";
            }
            echo("<td".$style.">".$squad->getStatus().$menu);
            echo("<tr>");
        }
        echo("</table>");
    }
?>