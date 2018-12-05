<?php

    ini_set("display_errors", 1);

    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/headObject.php");

    $error = $message = "";

    session_start();

    if (!isset($_SESSION['admin'])) {
        $error .= "This is a restricted site! You must be a logged-in admin to view its content!";
    }

    $head = new Head($pageTitle, $basePath);
    if (isset($_SESSION['admin'])) {
        $head->addMenuItem(true, "Squad overview", "squadOverview.php");
    }
    $head->display();

    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }
    else {
        include("content/squads.php");
    }

    include($basePath."includes/foot.php");

?>