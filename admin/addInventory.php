<?php
    
    ini_set("display_errors", 1);
    if (!isset($_SESSION['admin'])) {
        header("location:index.php");
    }

    $name = $ingameName = $price = "";
    $error = $message = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            //TODO: Upload img in ItemType !
            $item = new ItemType($_POST, "image", $basePath);
            $item->save($connection);
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
    sources</a> to avoid annoying erros in the game. This site should also serve as an image source.</b><br>
<i>I know this is boring copy-paste-work, automating the creation of Inventory is still a ToDo...</i>
<br><br><br>
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
            $classes = ItemClass::loadAll($connection);
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
            $sides = Side::loadAll($connection);
            foreach($sides as $side) {
                echo("<option value='".$side->getName()."'>".$side->getName()."</option>");
            }
        ?>
        </select>
        <!-- hidden input so we can identify this post-request in admin/index.php -->
        <input type="hidden" name="addItem", value="addItem">
    </div>

    <!-- Submit or reset -->
    <button type="submit" name="button" value="submit" class="btn btn-info">Submit</button>
    <button type="reset" name="button" value="reset" class="btn btn-warning">Reset</button>
</form>
