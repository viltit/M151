<?php
    session_start();
    if (!isset($_SESSION['admin'])) {
        header("location:/admin/index.php");
    }
    require_once($basePath."/validations/itemType.php");

    try {
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : null;
        $items = ItemType::loadAll($connection, $orderBy, null);

        //TODO: For some stupid reason I set ItemType.class as VARCHAR, ordering does not work

        echo("<h1>Inventory overwiev</h1><br><br>");
        echo("<table class='table'>
        <thead>
        <tr>
            <th scope='col'>Image</th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byName'>Name</a></th>
            <th scope='col'>Script Name</th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byPrice'>Price</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=byClass'>Class</a></th>
            <th scope='col'><a href='index.php?content=viewInventory&orderBy=bySide'>Side</a></th>
        </tr>
        </thead>");

        foreach($items as $item) {
            echo("<tr>");
            echo("<td width='15%'><img width='100%' src='".$basePath."/images/inventory/".$item->image().".jpg'></td>");
            echo("<td style='vertical-align:middle'>".$item->name()."</td>");
            echo("<td style='vertical-align:middle'>".$item->scriptName()."</td>");
            echo("<td style='vertical-align:middle'>".$item->price()."</td>"); 
            echo("<td style='vertical-align:middle'>".$item->class()."</td>");
            echo("<td style='vertical-align:middle'>".$item->side()."</td>"); 
        }
    }

    catch (InvalidArgumentException $e) {
        $error .= $e->getMessage();
    }

?>