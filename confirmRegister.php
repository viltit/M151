<?php

    session_start();
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }

    include("includes/head.php");
    echo("
        <h1>Welcome, ".$_SESSION['user']."</h1>
        <p>You are now registered and logged in!<p>
        <a href='index.php'>Back to the page</a>
    ");
    include("includes/foot.php");

?>