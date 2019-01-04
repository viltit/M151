<?php

    /*
    User can update his profile and password here

    TODO: When changing the username for the second time, we get an Error (user::load -> Object instead
    of string given)
    */

    //this seems like a sensible area to me, so we regenerate the session id
    session_start();
    session_regenerate_id();

    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }

    $errors = $message = "";

    ini_set("display_errors", 1);

    //load user data from database
    if (!isset($user)) {
        try {
            $user = User::load($connection, $_SESSION['user']);
        }
        catch (InvalidArgumentException $e) {
            $errors .= $e->getMessage();
        }
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($user)) {
        if (isset($_POST['updateProfile'])) {
            try {
                $user->update($connection, $_POST);
                if ($user->error() != "") {
                    $errors .= $user->error();
                }
                else {
                    //in case the user changed his username, update the session variable, or this site can not be reloaded ....
                    $_SESSION['user'] = $_POST['username'];
                    $message .= "Your userprofile has been updated.";
                }
            }
            catch (InvalidArgumentException $e) {
                $errors .= $e->getMessage();
            }
        }
        else if (isset($_POST['updatePassword'])) {
            //confirm old password
            if(!User::fromLogin($_SESSION['user'], $_POST['password'], $connection)) {
                $errors .= "You entered a wrong password.";
            }
            else try {
                $password = new Password($_POST['passwordNew'], $_POST['passwordNew2']);
                $user->updatePassword($connection, $password);
                $message .= "Your password has been updated.";
            }
            catch (InvalidArgumentException $e) {
                $errors .= $e->getMessage();
            }
        }
    }

    if (!empty($errors)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$errors."</div>";
    }
    else if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
?>

<!-- display the users personal information 
     this is ALMOST THE SAME code as in register.php -> TODO: Use same code for both
-->
<h1>My profile</h1>
<br>
<form action="index.php" method="POST">
    <!-- Name -->
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $user->name() ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- First Name -->
    <div class="form-group">
        <label for="firstName">First name *</label>
        <input type="text" name="firstName" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $user->firstName() ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- email -->
    <div class="form-group">
        <label for="email">E-Mail *</label>
        <input type="email" name="email" maxlength="40" required class="form-control"
                value="<?php echo $user->email() ?>"
                placeholder="Enter your E-Mail-Adress here">
    </div>
    <!-- username -->
    <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" name="username" minlength="3" maxlength="20" required class="form-control"
                value="<?php echo $user->username(); ?>"
                placeholder="3 to 20 characters">
    </div>
    <!-- Submit -->
    <button type="submit" name="updateProfile" value="submit" class="btn btn-info">Submit</button>
</form>
<br><br>
<h3>Change password</h3>
<form action = "" method = "POST">
    <!-- old password -->
    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" name="password" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>
    <!-- new password  -->
    <div class="form-group">
        <label for="password2">New Password *</label>
        <input type="password" name="passwordNew" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>
    <!-- confirm new password  -->
    <div class="form-group">
        <label for="password2">Confirm new Password *</label>
        <input type="password" name="passwordNew2" minlength="6" maxlength="20" required class="form-control"
                placeholder="6 to 20 characters">
    </div>
    <button type="submit" name="updatePassword" value="submit" class="btn btn-info">Submit</button>
</form>

<?php
    include("includes/foot.php");
?>