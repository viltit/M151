<?php

//TODO: This is VERY repeating code (the same as in index.php and squadOverview.php)
//=> Always display head with menus in index.php and include content via an $content variable
//one idea is to call this sites with a get param: index.php?content=inventoryOverwiev.php


    ini_set("display_errors", 1);
    session_start();

    $pageTitle = "Admin Panel";
    $basePath = "../";
    
    require_once($basePath."includes/headObject.php");
    require_once($basePath."validations/side.php");
    require_once($basePath."validations/squad.php");
    require_once($basePath."includes/database.php");
    require_once($basePath."validations/itemType.php");
    require_once($basePath."validations/itemClass.php");

    $db = new Database();
    $handler = $db->connect();

    //only let admins in:
    if (!isset($_SESSION['admin'])) {
        header("location:index.php");
    }

    //display menu:
    $head = new Head($pageTitle, $basePath);
    $head->addMenuItem(true, "Squad managment", "squadOverview.php");
    $head->addMenuItem(true, "Inventory managment", "inventoryOverwiev.php"); 
    $head->addMenuItem(false, "Logout", "logout.php");
    $head->display();

    $name = $ingameName = $price = "";
    $error = $message = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            //TODO: Upload img in ItemType !
            $item = new ItemType($_POST, "image", $basePath);
            $item->save($handler);
            $message .= "The new item is now saved in the database.";
        }
        catch (Exception $e) {
            $error .= $e->getMessage();
        }
    }

    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }
?>

<h1>Add new Item Types</h1>
<b>Please check your input with <a href="https://community.bistudio.com/wiki/Arma_3_CfgWeapons_Weapons">official 
    sources</a> to avoid annoying erros in the game. This site should also serve as an image source.</b><br><br><br>
<form action="" method="POST" enctype="multipart/form-data">
    <!-- Name -->
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $name ?>"
                placeholder="The name shown on this site">
    </div>
    <!-- Script name -->
    <div class="form-group">
        <label for="ingameName">Ingame name (used for scripting) *</label>
        <input type="text" name="ingameName" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $ingameName ?>"
                placeholder="Name used in scripts. This field MUST be correct.">
    </div>
    <!-- image -->
    <div class="form-group">
        <label for="image">Image *</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
        <input type="file" name="image" minlength="3" maxlength="20" required class="form-control-file">
        <!-- TODO: Preview of already uploaded image -->
    </div>
    <!-- price -->
    <div class="form-group">
        <label for="price">Price *</label>
        <input type="number" name="price" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $price ?>"
                placeholder="Please do not decide the price of an item for yourself.">
    </div>
    <!-- Item Class. Needs a selection -->
    <div class="form-group">
        <label for="itemClass">Item class *</label>
        <select class='form-control' name="class">
        <?php
            $classes = ItemClass::loadAll($handler);
            print_r($classes);
            foreach($classes as $class) {
                echo("<option value='".$class->getName()."'>".$class->getName()."</option>");
            }
        ?>
        </select>
    </div>
    <!-- Side. Also needs a selection -->
    <div class="form-group">
        <label for="side">Side *</label>
        <select class='form-control' name="side">
        <option value="all">All</option>
        <?php
            $sides = Side::loadAll($handler);
            foreach($sides as $side) {
                echo("<option value='".$side->getName()."'>".$side->getName()."</option>");
            }
        ?>
        </select>
    </div>

    <!-- Submit or reset -->
    <button type="submit" name="button" value="submit" class="btn btn-info">Submit</button>
    <button type="reset" name="button" value="reset" class="btn btn-warning">Reset</button>
</form>
