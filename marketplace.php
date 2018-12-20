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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buyItem'])) {
    /* 
    TODO: 
    1) check if the squad has enough credits
    2) add item to the squads inventory
    3) save the transaction in the database
    */
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
echo("
    <div class='d-flex'>
        <div class='p-2' style='width:50%;'>
            <h2>Inventory</h2>
        </div>
        <div class='p-2' style='width:50%;'>
            <h2>Market</h1>");
                listMarket($market);
        echo("
        </div>
    </div>
");


include("includes/foot.php");

//helper function
function listMarket($market) {
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
    foreach($market as $item) {
        echo("<tr>
                <td style='vertical-align:middle;'>
                    <div id='".$item->name()."'>
                    <form class='form-group' name='activate' action='marketplace.php#".$item->name()."' method='POST' id='activate".$item->name()."'>
                        <input type='hidden' name='name' value='".$item->name()."'>
                        <input type='submit' class='btn btn-success' name='buyItem' value='Buy'></form>
                    </form>
                </td>
                <td style='width:40%; vertical-align:middle;'>
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