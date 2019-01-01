<?php

    session_start();

    $pageTitle = "Register yourself";

    ini_set("display_errors", 1);
    include("includes/head.php");
    include_once("validations/user.php");
    include_once("includes/database.php");

    $message = $errors = $user = $name = $firstName = $email = $username = "";

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        try { 
            $user = new User($_POST);
            if (empty($user->error())) {
                $db = new Database();
                $handler = $db->connect();
                try { 
                    $user->save($handler);
                }
                catch (InvalidArgumentException $e) {
                    $errors .= $e->getMessage();
                }
                //again, check for errors
                if (empty($user->error())) {
                    $message = "Your registration was succesful.";
                    //log the user in:
                    $_SESSION['user'] = $user->username();
                    header("location:confirmRegister.php");
                }
                else {
                    $errors .= $user->error();
                }
            }
            else {
                $errors .= $user->error();
            }
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

?>
    

<form action="" method="POST">
    <!-- Name -->
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $name ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- First Name -->
    <div class="form-group">
        <label for="firstName">First name *</label>
        <input type="text" name="firstName" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $firstName ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- email -->
    <div class="form-group">
        <label for="email">E-Mail *</label>
        <input type="email" name="email" maxlength="40" required class="form-control"
                value="<?php echo $email ?>"
                placeholder="Enter your E-Mail-Adress here">
    </div>
    <!-- username -->
    <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" name="username" minlength="3" maxlength="20" required class="form-control"
                placeholder="3 to 20 characters">
    </div>
    <!-- password -->
    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" name="password" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>
    <!-- password confirm -->
    <div class="form-group">
        <label for="password2">Confirm Password *</label>
        <input type="password" name="password2" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>

    <!-- Submit or reset -->
    <button type="submit" name="button" value="submit" class="btn btn-info">Submit</button>
    <button type="reset" name="button" value="reset" class="btn btn-warning">Reset</button>
</form>


<?php
    include("includes/foot.php");
?>