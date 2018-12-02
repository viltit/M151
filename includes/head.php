<?php  
   
   /*
   TODO:
   - Captcha for registration ?
   */

    ini_set("display_errors", 1);
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    //basePath is a variable introduced while working on the admin pages, which are in a subfolder. 
    if (!isset($basePath)) {
        $basePath = "";
    }

    $error = $message =  '';
    $username = $password = '';

    /* 
    Check username and password if user tried to login
    */
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        require_once("validations/user.php");

        //reset messages
        $error = $message =  ''; 
        $username = $password = '';

        //first, check if the user wanted to log-out or log in
        if (isset($_POST['logout'])) {
            require_once("includes/logout.php");
        }  
        else if (isset($_POST['username'])) {
            //username. We used htmlspecialchars in register.php, so we use it here too
            if(isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 20) {
                $username = htmlspecialchars(trim($_POST['username']));
            } else {
                $error .= "Please enter your username.<br />";
            }
            //password
            if(isset($_POST['password']) && !empty(trim($_POST['password'])) && strlen(trim($_POST['password'])) <= 20) {
                $password = htmlspecialchars(trim($_POST['password']));
            } else {
                $error .= "Please enter your password.<br />";
            }
            
            //no errors? Check if username with password exists
            if (empty($error)) {
                require_once("includes/database.php");
                $db = new Database();
                $handler = $db->connect();   
                $status = User::fromLogin($username, $password, $handler);
                if ($status) { 
                    $message = "Hello ".$_SESSION['user']."! You are now logged in.";
                }
                else {
                    $error .= "Invalid username and / or password";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $pageTitle; ?></title>
        <!-- CSS from bootsrap 
        TODO: We seem to have a wrong version (no dark tables etc.)
        -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    </head>

    <body>
        <nav class="navbar navbar-dark bg-dark navbar-fixed-top navbar-expand-lg">
            <a class="navbar-brand" href="#"><img src="images/arma3Logo.png" class="img-fluid"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <!-- Forum (dead link) -->
                    <a class="nav-link" href="">Forum<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item active">
                    <!-- Squad site -->
                    <a class="nav-link" href="squadPage.php">My Squad<span class="sr-only">(current)</span></a>
                </li>
            </ul>
            <!-- User not linked to a session -> display login form and register button -->
            <?php
                if (!isset($_SESSION['user'])) {
            ?>
            <form class="form-inline my-2 my-lg-0" method="POST">
                <input type="text" class="form-control mr-sm-2" name="username" placeholder="Username">
                <input type="password" class="form-control mr-sm-2" name="password" placeholder="Password">
                <button type="submit" class="btn btn-default">Sign In</button>
            </form>
            <ul class="navbar-nav mr-sm-2">
                <li class="nav-item active"><a class="nav-link" href="register.php"><b>Not a user yet? Register</b></a>
                </li>
            </ul>
            <!-- user is logged in -> display logout button -->
            <?php
                }
                else {
            ?>
            <form class="navbar-form navbar-right" method="POST">
                <button type="submit" name="logout" class="btn btn-default">Logout</button>
            </form>
            <?php
                }
            ?>
            </div>
            </div>
        </nav>
        <!-- inline style: get some space between navbar and content -->
        <div class="container" style="padding-top: 40px;">
            <?php
                if (!empty($message)) {
                    echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
                }
                if (!empty($error)) {
                    echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
                }

            ?>