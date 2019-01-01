<?php

$basePath = $error = $message = $isLeader = "";
$pageTitle = "Marketplace";
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : "";

ini_set("display_errors", 1);

require_once("validations/squad.php");
require_once("validations/inventory.php");
require_once("includes/database.php");
require_once("validations/itemType.php");
require_once("validations/name.php");
require_once("includes/squadStatus.php");

session_start();
session_regenerate_id();

//no user linked to the session? just return ...
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}

include("includes/head.php");

$db = new Database();
$connection = $db->connect();

//check the squads status
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
else {
    $inventory = new Inventory($connection, $squad->getName());
    $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : "";
    $market = ItemType::loadAll($connection, $orderBy);
    //check for an item to buy:
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            if (isset($_POST['buyItem']) && isset($_POST['name'])) {
                $inventory->add($connection, $_POST['name']);
                $squad->setCredits($squad->getCredits() - $_POST['price']);
                //TODO: When do we update the squads credits in the database? After EVERY purchase seems like a waist
                //TODO: Let the player buy stuff in a shoping-cart and only make a transaction after shopping-cart is
                //finished
                $squad->updateCredits($connection);
                unset($_POST['name']); //TODO Look at Post-Redirect-Get-Pattern
            }
            else if (isset($_POST['sellItem']) && isset($_POST['name'])) {
                $inventory->remove($connection, $_POST['name']);
                $squad->setCredits($squad->getCredits() + $_POST['price']);
                //same todos as above
                $squad->updateCredits($connection);
                unset($_POST['name']); 
            }
        }
        catch (InvalidArgumentException $e) {
            $error .= $e->getMessage();
        }
    }
    //list the squads inventory on the left side, list the marketplace on the right side
    //the div's here are bootstraps flexbox
    //THIS PART WOULD REALLY BENEFIT FROM AJAX AND OR / JQUERY, but I can't be bothered to learn these too now
    echo("<h1>Market</h1><br>");
    echo("<b>Your credits: ".$squad->getCredits()."</b><br><br>");
    listMarket($inventory, $market, $squad, $orderBy);
}        


include("includes/foot.php");

//helper functions
//TODO: Code seems more complex than necessary
function listMarket($inventory, $market, $squad, $orderBy) {
    $inventoryItems = $inventory->items();
    $ingameItems = $inventory->ingameItems();
    echo("<table class='table'>
    <thead>
    <tr>
        <th scope='col'>You have</th>
        <th scope='col'></th>
        <th scope='col'>Image</th>
        <th scope='col'><a href='marketplace.php?orderBy=byName'>Name</a></th>
        <th scope='col'><a href='marketplace.php?orderBy=byPrice'>Price</a></th>
        <th scope='col'><a href='marketplace.php?orderBy=byClass'>Class</a></th>
        <th></th>
    </tr>
    </thead>");
    foreach($market as $item) {
        //squads items in storage:
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
                    <form class='form-group' name='buy' action='marketplace.php?orderBy=".$orderBy."#".$item->name()."' method='POST' id='activate".$item->name()."'>
                        <input type='hidden' name='name' value='".$item->name()."'>
                        <input type='hidden' name='price' value='".$item->price()."'>
                        <input type='hidden' name='buyItem' value='true'>
                        <button type='submit' ".$buyStatus.">
                            <span>".$buyButton."</span> 
                        </button>
                    </form>
                    <form class='form-group' name='sell' action='marketplace.php?orderBy=".$orderBy."#".$item->name()."' method='POST' id='activate".$item->name()."'>
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

function listInventory($market, $inventory, $squad) {
    $inventoryItems = $inventory->items();
    print_r($inventoryItems);
    echo("<table class='table'>
    <thead>
    <tr>
        <th scope='col'></th>
        <th scope='col'>Image</th>
        <th scope='col'><a href='marketplace.php?orderBy=byName'>Name</a></th>
        <th scope='col'><a href='marketplace.php?orderBy=byPrice'>Price</a></th>
        <th scope='col'><a href='marketplace.php?orderBy=byClass'>Class</a></th>
        <th></th>
    </tr>
    </thead>");
        //problem: we save each item in the database -> how to get the number?
        foreach($market as $item) {
            echo($item->name());
           
            echo("<tr>
                    <td style='vertical-align:middle;'>".$item->name()."</td>
                    <td style='vertical-align:middle;'>".$count."</td>
            ");
        }
    echo("</table>");
}

?>