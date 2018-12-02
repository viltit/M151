<?php
    ini_set("display_errors", 1);

    session_start();
    session_regenerate_id();

    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/headObject.php");

    $head = new Head($pageTitle, $basePath);
    $head->addMenuItem(true, "Pending squads", "squadValidate.php");

    $head->display();

    //include("includes/head.php");
?>

<h1 class="form-heading">Admin-Login</h1>
<div class="login-form">
<div class="main-div">
    <div class="panel">
    <p>Identify yourself, please!<br>
    <i>This is a restricted area, and we WILL log your ip when you try to log in.</i>
    </p>
   </div>
    <form id="Login">
        <div class="form-group">
            <input type="text" class="form-control" id="name" placeholder="Your playername">
        </div>
        <div class="form-group">
            <input type="password" class="form-control" id="password" placeholder="Your Password">
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    </div>
</div></div></div>

<?php
    include($basePath."includes/foot.php");
?>