<?php  
   
   /*
   TODO:
   - Dont show login or register for an already logged in user. Change button to "logout" in this case.
   - Captcha for registration ?
   */

    ini_set("display_errors", 1);

    $error = $message =  '';
    $username = $password = '';

    ini_set("display_errors", 1);

    /* 
    Check username and password if user tried to login
    */
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        //username. We used htmlspecialchars in register.php, so we use it here too
        if(isset($_POST['username']) && !empty(trim($_POST['username'])) && strlen(trim($_POST['username'])) <= 20) {
            $username = htmlspecialchars(trim($_POST['username']));
        } else {
            $error .= "Invalid username.<br />";
        }
        //password
        if(isset($_POST['password']) && !empty(trim($_POST['password'])) && strlen(trim($_POST['password'])) <= 20) {
            $password = htmlspecialchars(trim($_POST['password']));
        } else {
            $error .= "Invalid username.<br />";
        }
        //no errors? Check if username with password exists
        if (empty($error)) {
            require("includes/dbConnect.php");
            $query = "SELECT username FROM Player WHERE username = ? AND password = UNHEX(SHA1(?))";
            $stm = $mysqli->prepare($query);
            $stm->bind_param("ss", $username, $password);
            $stm->execute();

            $result = $stm->get_result();

            //we have the result -> close database connection
            $stm->close();
            $mysqli->close();

            //TODO: Set session variable for succesful login
            if ($result->num_rows != 1) {
                $error .= "Invalid username and / or password.";
            }
            else {
                $message = "You are now logged in.";
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
        <!-- CSS from bootsrap -->
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    </head>

    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Arma 3 League</a>
                </div>
                <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-left">
                    <li><a href="index.php">Home</a>
                    </li>
                    <li><a href="">Forum</a>
                    </li>
                    <!-- If a dropdown would be needed, here is a template 
                    <li class="dropdown">
                    <a class="dropdown-toggle nolink-pointer" data-toggle="dropdown">Dropdown 1 <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="">Link</a>
                        </li>
                    </ul>
                    </li>
                    <-->
                    <li><a href="">My Squad</a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="register.php"><b>Not a user yet? Register</b></a>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" placeholder="Username">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="password" placeholder="Password">
                    </div>
                    <button type="submit" class="btn btn-default">Sign In</button>
                </form>
                </div>
                </div>
            </div>
        </nav>
        <!-- inline style: bad way of cirumventing bootstraps 50px navbar which I want always on top -->
        <div class="container" style="padding-top: 80px;">
            <?php
                if (!empty($message)) {
                    echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
                }
                if (!empty($error)) {
                    echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
                }
                include "content/".$content.".cont.php";
            ?>