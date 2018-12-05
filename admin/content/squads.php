<?php
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    //we do not care about an error message here, just redirect
    
    if (!isset($_SESSION['admin'])) {
        header("location:../index.php");
    }

    //display all squads and their status
    //TODO: Option to list alphabeticly or by status. For now, we list by status
    
    require_once("../includes/database.php");    
    $db = new Database();
    $handler = $db->connect();
    
    //we make several queries here, i am sure it could be done in one
    $query = "SELECT * FROM Squad";
    $stm = $handler->prepare($query);
    $stm->execute();

    //also, prepare the queries for the leader name and the players of each squad:
    $leaderQuery = "SELECT username FROM Player WHERE id = :id";
    $leaderStm = $handler->prepare($leaderQuery);
    $playerQuery = "SELECT username FROM Player WHERE squadID = :id";
    $playerStm = $handler->prepare($playerQuery);

    //TODO: Can't we work with class squad here ?
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
            </thead>"
        );
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        echo("<tr>");
        echo("<td>".$row['name']."</td>");
        echo("<td>".$row['url']."</td>");
        //todo: Image
        echo("<td></td>");
        //leader -> needs a new query
        $leaderStm->bindParam(":id", $row['leaderID']);
        $leaderStm->execute();
        $subRow = $leaderStm->fetch(PDO::FETCH_ASSOC);
        echo("<td>".$subRow['username']."</td>");
        //players -> needs another query 
        $playerStm->bindParam(":id", $row['id']);
        $playerStm->execute();
        $players = "";
        while ($playerRow = $playerStm->fetch(PDO::FETCH_ASSOC)) {
            $players .= $playerRow['username']."<br>";
        }
        $players = substr($players, 0, -4); //remove last <br>
        echo("<td>".$players."</td>");

        //TODO: Print side name, not id
        echo("<td>".$row['sideID']);
        //status: Mark red when the squad is pending. Add button for activation menu
        $style = "";
        $menu = "";
        if ($row['status'] == 'pending') {
            $style = " class='table-danger'";
            $menu = "<br><a href='squadOverview.php?name=".$row['name']."&action=activate'>activate</a>";
        }
        else if ($row['status'] == 'inactive') {
            $style = " class='table-warning'";
        }
        echo("<td".$style.">".$row['status'].$menu);
        echo("<tr>");
    }
    echo("</table>");
?>