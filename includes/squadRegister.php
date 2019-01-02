<?php
        ini_set("display_errors", 1);
        $pageTitle = "Register a new squad";
        include("includes/head.php");
    
        require_once("includes/database.php");
        require_once("validations/squad.php");
        require_once("validations/inventory.php");
    
        $errors = "";
        $message = "";

        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id();

        if (!isset($_SESSION['user'])) {
            echo ("<div class=\"alert alert-danger\" role=\"alert\">You must be logged in to view this page.</div>");
        }
        else {

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                try {
                    $squad = new Squad($_POST);
                    $db = new Database();
                    $connection = $db->connect();
                    $squad->save($connection); 
                    $message = "Your squad was registered! If you have not done so yet, please talk to an admin now: 
                    You still need to choose a side in our online conflict! Only admins can assign you to a side.";
                }
                catch (InvalidArgumentException $e) {
                    $errors .= $e->getMessage();
                }
            }

            if (!empty($errors)) {
                echo "<div class=\"alert alert-danger\" role=\"alert\">".$errors."</div>";
            }
            else if (!empty($message)) {
                echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
            }


            //display squad registration page:
            ?>
            <form action="" method="POST">
                <!-- Squad Name -->
                <div class="form-group">
                    <label for="Squad name">Suqad name *</label>
                    <input type="text" name="name" minlength="3" maxlength="20" required class="form-control"
                            placeholder="3 to 20 characters">
                </div>
                <!-- Suqad url -->
                <div class="form-group">
                    <label for="url">Squad Homepage</label>
                    <input type="text" name="url" minlength="3" maxlength="20" class="form-control"
                            placeholder="Valid url">
                </div>
                <!-- Image TODO: Needs file upload  
                <div class="form-group">
                    <label for="img">E-Mail *</label>
                    <input type="email" name="email" maxlength="40" required class="form-control"
                            value=""
                            placeholder="Enter your E-Mail-Adress here">
                </div>
                -->
                <!-- Squadleader 
                     TODO: This value can not be changed !
                -->
                <div class="form-group">
                    <label for="leadername">Squadleader *</label>
                    <input type="text" name="leadername" minlength="3" maxlength="20" required readonly class="form-control"
                        value="<?php echo $_SESSION['user'];?>"    
                        placeholder="Username of the Squadleader">
                </div>
                <!-- TODO: user password for increased security ? 
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" name="password" minlength="6" maxlength="20" required class="form-control"
                            placeholder="6 to 20 characters">
                </div>
                -->

                <!-- TODO: Add input fields for new players dynamicly 
                     For now, we just take several players with comma separation
                -->

                <div class="form-group">
                    <label for="playername">Players *</label>
                    <input type="text" name="players" minlength="3" maxlength="100" required class="form-control"    
                        placeholder="List all your Players with their username, separate them with a comma - ie Player1, Player2">
                </div>


                <!-- Submit or reset -->
                <button type="submit" name="button" value="submit" class="btn btn-info">Submit</button>
                <button type="reset" name="button" value="reset" class="btn btn-warning">Reset</button>
            </form>
            <?php
        }
        


?>