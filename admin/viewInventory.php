<?php
    ini_set("display_errors", 1);

    session_start();
    if (!isset($_SESSION['admin'])) {
        header("location:/admin/index.php");
    }
    require_once($basePath."/validations/itemType.php");

    //first, check if we have a post params. If so, update item
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['updateItem'])) {
            try {
                $item = new ItemType($_POST, "", $basePath);
                $item->update($connection, $_POST['oldScriptName']);
            }
            catch (InvalidArgumentException $e) {
                echo ("<h1>".$e."</h1>");
            }
        }
    }

    //display errors or messages
    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }

    //TODO: Search function

    try {
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : null;
        $items = ItemType::loadAll($connection, $orderBy, null);

        echo("<h1>Inventory overwiev</h1><br><br>");
        echo("<table class='table'>
        <thead>
        <tr>
            <th scope='col'>Image</th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byName'>Name</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byScript'>Script Name</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byPrice'>Price</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byClass'>Class</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=bySide'>Side</a></th>
            <th></th>
        </tr>
        </thead>");

        foreach($items as $item) {
            echo("<tr>");
            echo("<form action='index.php' method='post' class='form-inline my-2 my-lg-0'>");
            //TODO: Ability to change image
            echo("<td width='15%'><img width='100%' src='".$basePath."/images/inventory/".$item->image().".jpg'></td>");
            echo("<input type='hidden' name='image' value='".$item->image()."'></input>");
            //to update an item, we also need its old script name as identifier
            echo("<input type='hidden' name='oldScriptName' value='".$item->scriptName()."'></input>");
            echo("<td style='vertical-align:middle'>
                    <input class='form-control' value='".$item->name()."' 
                    required name='name'></input></td>");
            echo("<td style='vertical-align:middle'>
                    <input class='form-control' value='".$item->scriptName()."' 
                    required name='ingameName'></input></td>");
            echo("<td style='vertical-align:middle'>
                    <input class='form-control' value='".$item->price()."' 
                    required type='number' name='price'></input></td>"); 
            //I tried to do a dropdown here, but it was too tedious
            echo("<td style='vertical-align:middle'>
                    <input class='form-control' value='".$item->class()."'
                    required type = 'text' name='class'></input></td>");
            echo("<td style='vertical-align:middle'>
                    <input class='form-control' value='".$item->side()."' 
                    required type='text' name='side'></input></td>"); 
            echo("</select></td>");
            echo("<td style='vertical-align:middle'><input type='submit' value='update' name='updateItem'></input></td>");
            echo("</form></tr>");
        }
    }

    catch (InvalidArgumentException $e) {
        $error .= $e->getMessage();
    }

?>