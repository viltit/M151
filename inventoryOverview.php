<?php

    /*
    Display inventory and add possibility to tranfer items to the game
    code has many similiarities to marketplace.php, however, it is not the same
    */

    ini_set("display_errors", 1);
    $pageTitle = "My Squad";
    $message = $error = "";

    include("includes/head.php");

    require_once("includes/database.php");
    require_once("validations/squad.php");
    require_once("validations/inventory.php");
    require_once("validations/user.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    //this is a sensible area (do NOT let the enemy see your inventory). So we regenerate the session:
    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        header("location: index.php");
    }
    else if (!isset($_SESSION['squadLeader'])) {
        $message .= "You are not the leader of your squad. You can view this page, but you are not allowed
                to make any transactions.";
    }

    $db = new Database();
    $connection = $db->connect();

    //TODO: Identical database-access like in squadOverwiew -> set a session variable ?
    $squad = Squad::load($connection, $_SESSION['user']);
    $inventory = new Inventory($connection, $squad->getName());

    //display errors or messages
    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }    

    if ($inventory->status() == "open") {
        displayInventory($inventory);
    }
    else {
        echo("<h1>CLOSED</h1>");
    }

    /*
    else if ($inventory->status() == "pending") {
        //todo
    }
    else if ($inventory->status() == "closed") {
        //todo
    } */

    include("includes/foot.php");

    //helper function
    function displayInventory(Inventory $inventory) {
        echo("
            <table class='table'>
                <th>Image</th>
                <th>Name</th>
                <th>Class</th>
                <th>In storage</th>
                <th>In game</th>
            </table>
        ");
        //TODO: Better way of storing ingame and instore items !
        //TODO: Do NOT load the whole marketplace here! Very inefficient
        foreach($inventory->items() as $item => $number) {
            echo("<tr>");
            echo("<td>ITEM</td>");
            echo("<td style='width:25%; vertical-align:middle;'>
            <a href='images/inventory/".$item->image().".jpg' target='_blank'> 
                <img src='images/inventory/".$item->image().".jpg' width='100%'>
            </a></td>
                ");
            echo("<tr>");

            $count1 = isset($inventoryItems[$item->name()->string()]) ? $inventoryItems[$item->name()->string()] : 0;
            //squads items in game
            $count2 = isset($ingameItems[$item->name()->string()]) ? $ingameItems[$item->name()->string()] : 0;
            
            //enable or disable button to sell and buy this item
            $buyStatus = "class='btn btn-success'";
            $buyButton = "&#8592;"; //arrow left
            if ($squad->getCredits() < $item->price() || !(isset($_SESSION['squadLeader']))) {
                $buyStatus = "disabled class='btn btn-warning'";
                $buyButton = "&#215;"; //cross
            }
            $sellStatus = "class='btn btn-success'";
            $sellButton = "&#8594;";
            if ($count1 == 0 || !(isset($_SESSION['squadLeader']))) {
                $sellStatus = "disabled class='btn btn-warning'";
                $sellButton = "&#215;";
            }
    
            echo("<tr>
                    <td style='vertical-align:middle'><span class='text-success'>".$count1."</span>
                        +<span class='text-warning'>".$count2."<span/></td>
                    <td style='vertical-align:middle;'>
                        <div id='".$item->name()."'>
                        <form class='form-group' name='buy' action='marketplace.php#".$item->name()."' method='POST' id='activate".$item->name()."'>
                            <input type='hidden' name='name' value='".$item->name()."'>
                            <input type='hidden' name='price' value='".$item->price()."'>
                            <input type='hidden' name='buyItem' value='true'>
                            <button type='submit' ".$buyStatus.">
                                <span>".$buyButton."</span> 
                            </button>
                        </form>
                        <form class='form-group' name='sell' action='marketplace.php#".$item->name()."' method='POST' id='activate".$item->name()."'>
                        <input type='hidden' name='name' value='".$item->name()."'>
                        <input type='hidden' name='price' value='".$item->price()."'>
                        <input type='hidden' name='sellItem' value='true'>
                        <button type='submit' ".$sellStatus.">
                            <span>".$sellButton."</span> 
                        </button>
                    </form>
                    </td>
                    <td style='width:25%; vertical-align:middle;'>
                        <a href='images/inventory/".$item->image().".jpg' target='_blank'> 
                            <img src='images/inventory/".$item->image().".jpg' width='100%'>
                        </a></td>
                    <td style='vertical-align:middle;'>".$item->name()."</td>
                    <td style='vertical-align:middle;'>".$item->price()."</td>
                    <td style='vertical-align:middle;'>".$item->class()."</td>
            ");

        }

    }


?>