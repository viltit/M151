<?php

$basePath = $error = $message = "";
$pageTitle = "Marketplace";
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : "";

ini_set("display_errors", 1);

require_once("validations/squad.php");
require_once("validations/inventory.php");
require_once("includes/database.php");
require_once("validations/itemType.php");
require_once("validations/name.php");

session_start();
session_regenerate_id();

//no user linked to the session? just return ...
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}

include("includes/head.php");

$db = new Database();
$connection = $db->connect();

try {
    //TODO IMPORTANT: Do NOT load from database EVERY TIME a user buys an item 
    //TODO IMPORTANT: LOOK AT "spl_autoload_register"
    //if (!isset($_SESSION['squad'])) {
        $squad = Squad::load($connection, $_SESSION['user']);
    //    $_SESSION['squad'] = $squad;
    //}

    //else {
    //    $squad = $_SESSION['squad'];
    //}

    $inventory = new Inventory($connection, $squad->getName());
    $market = ItemType::loadAll($connection, $orderBy, $squad->getSide());
}
catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
}

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

//display errors or messages
if (!empty($message)) {
    echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
}
if (!empty($error)) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
}

//list the squads inventory on the left side, list the marketplace on the right side
//the div's here are bootstraps flexbox
//THIS PART WOULD REALLY BENEFIT FROM AJAX AND OR / JQUERY, but I can't be bothered to learn these too now
echo("<h1>Market</h1><br>");
echo("<b>Your credits: ".$squad->getCredits()."</b><br><br>");
listMarket($inventory, $market, $squad);
    


include("includes/foot.php");

//helper functions
function listMarket($inventory, $market, $squad) {
    $inventoryItems = $inventory->items();
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
        $count = isset($inventoryItems[$item->name()->string()]) ? $inventoryItems[$item->name()->string()] : 0;
        $buyStatus = "class='btn btn-success'";
        $buyButton = "&#8592;"; //arrow left
        if ($squad->getCredits() < $item->price()) {
            $buyStatus = "disabled class='btn btn-warning'";
            $buyButton = "&#215;"; //cross
        }
        $sellStatus = "class='btn btn-success'";
        $sellButton = "&#8594;";
        if ($count == 0) {
            $sellStatus = "disabled class='btn btn-warning'";
            $sellButton = "&#215;";
        }

        echo("<tr>
                <td style='vertical-align:middle'>".$count."</td>
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