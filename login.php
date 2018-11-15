<?php
    $error = $message =  '';
    $username = $password = '';

    ini_set("display_errors", 1);

    // Do we have Data in Post?
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

            //TODO: These messages are getting lost after redirect
            if ($result->num_rows != 1) {
                $error .= "Invalid username and / or password.";
            }
            else {
                $message = "You are now logged in.";
            }
            header("location:index.php");
        }
    }
?>