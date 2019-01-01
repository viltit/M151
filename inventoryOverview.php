<?php

    /*
    Display inventory and add possibility to tranfer items to the game
    code has many similiarities to marketplace.php, however, it is not the same
    */

    ini_set("display_errors", 1);
    $pageTitle = "My Squad";
    $message = $error = "";

    include("includes/head.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    //this is a sensible area (do NOT let the enemy see your inventory). So we regenerate the session:
    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        header("location: index.php");
    }

    require_once("includes/database.php");
    require_once("validations/squad.php");
    require_once("validations/inventory.php");
    require_once("validations/user.php");
    require_once("includes/squadStatus.php");

    //check the squads status
    $db = new Database();
    $connection = $db->connect();
    $status = getSquadStatus($connection);
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
        //TODO: Identical database-access like in squadOverwiew -> set a session variable ?
        $inventory = new Inventory($connection, $squad->getName()); 
        $market = ItemType::loadAll($connection);

        //check for post-data:
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['toGame']) && isset($_POST['name'])) {
               $inventory->toGame($connection, $_POST['name'], true);
            }
            else if (isset($_POST['toCamp']) && isset($_POST['name'])) {
                $inventory->toGame($connection, $_POST['name'], false);
            }
        }

        if ($inventory->status() == "open") {
            displayInventory($inventory, $market);
        }
        else if ($inventory->status() == "closed") {
            echo("
                <h1>CLOSED</h1>
                <p>There is currently a game running! All inventorys are closed during this time!</p>
            ");
        }
        /*
        else if ($inventory->status() == "pending") {
            //todo
        }
        */
    }

    include("includes/foot.php");

    //helper function
    function displayInventory(Inventory $inventory, $market) {
        echo("
            <table class='table'><tr>
                <th>Image</th>
                <th>Name</th>
                <th>Class</th>
                <th>Price</th>
                <th>In storage</th>
                <th></th>
                <th>In game</th>
            </tr>
        ");
        //TODO: Better way of storing ingame and instore items !
        //TODO: Do NOT load the whole marketplace here! Very inefficient => rewrite inventory.php to store
        //an array of items instead of strings
        $inventoryItems = $inventory->items();
        $ingameItems = $inventory->ingameItems();
        foreach($market as $item) {
            //squads items in storage:
            $count1 = isset($inventoryItems[$item->name()->string()]) ? $inventoryItems[$item->name()->string()] : 0;
            //squads items in game
            $count2 = isset($ingameItems[$item->name()->string()]) ? $ingameItems[$item->name()->string()] : 0;
            //do not display item if squad has none
            if ($count1 == 0 && $count2 == 0) {
                continue;
            }
            //prepare buttons
            $toGameStatus = "class='btn btn-success'";
            $toGameButton = "&#8594;"; //arrow-right
            if ($count1 == 0 || !(isset($_SESSION['squadLeader']))) {
                $toGameStatus = "disabled class='btn btn-warning'";
                $toGameButton = "&#215;"; //cross
            }
            $toCampStatus = "class='btn btn-success'";
            $toCampButton = "&#8592;"; //arrow-right
            if ($count2 == 0 || !(isset($_SESSION['squadLeader']))) {
                $toCampStatus = "disabled class='btn btn-warning'";
                $toCampButton = "&#215;"; //cross
            }

            echo("<tr>");
            echo("
                <td style='width:25%; vertical-align:middle;'>
                    <a href='images/inventory/".$item->image().".jpg' target='_blank'> 
                    <img src='images/inventory/".$item->image().".jpg' width='100%'>
                </a></td>
                <td style='vertical-align:middle;'>".$item->name()."</td>
                <td style='vertical-align:middle;'>".$item->class()."</td>
                <td style='vertical-align:middle;'>".$item->price()."</td>
                <td style='vertical-align:middle;'>".$count1."</td>
                <td style='vertical-align:middle;'>
                <div id='".$item->name()."'>
                    <form class='form-group' name='toGame' action='inventoryOverview.php#".$item->name()."' method='POST' id='".$item->name()."'>
                        <input type='hidden' name='name' value='".$item->name()."'>
                        <input type='hidden' name='toGame' value='true'>
                        <button type='submit' ".$toGameStatus.">
                            <span>".$toGameButton."</span> 
                        </button>
                    </form>
                    <form class='form-group' name='toCamp' action='inventoryOverview.php#".$item->name()."' method='POST' id='".$item->name()."'>
                        <input type='hidden' name='name' value='".$item->name()."'>
                        <input type='hidden' name='toCamp' value='true'>
                        <button type='submit' ".$toCampStatus.">
                            <span>".$toCampButton."</span> 
                        </button>
                    </form>
                    </div>
                </td>
                <td style='vertical-align:middle;'>".$count2."</td>
                ");

            echo("</tr>");

            /*
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
            */
        }

    }


?>