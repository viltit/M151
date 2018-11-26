<?php
    ini_set("display_errors", 1);
    $pageTitle = "Admin Panel";
    $basePath = "../";
    include ($basePath."includes/head.php");

    $head = new Head($pageTitle, $basePath);
    $head->display();

    //include("includes/head.php");

    //include("includes/foot.php");
?>

<h1 class="form-heading">login Form</h1>
<div class="login-form">
<div class="main-div">
    <div class="panel">
   <h2>Admin Login</h2>
   <p>Please enter your email and password</p>
   </div>
    <form id="Login">
        <div class="form-group">
            <input type="email" class="form-control" id="inputEmail" placeholder="Email Address">

        </div>

        <div class="form-group">

            <input type="password" class="form-control" id="inputPassword" placeholder="Password">

        </div>
        <div class="forgot">
        <a href="reset.html">Forgot password?</a>
</div>
        <button type="submit" class="btn btn-primary">Login</button>

    </form>
    </div>
<p class="botto-text"> Designed by Sunil Rajput</p>
</div></div></div>


</body>
</html>