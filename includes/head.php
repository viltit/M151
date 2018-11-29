<?php  
   
   /*
   TODO:
   - Dont show login or register for an already logged in user. Change button to "logout" in this case.
   - Captcha for registration ?
   */

    ini_set("display_errors", 1);
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }


    class Head {

        private $pageTitle = "";
        private $menuItemsLeft = array();
        private $menuItemsRight = array();
        private $basePath = "";


        public function __construct(String $pageTitle, string $basePath) {
            $this->pageTitle = $pageTitle;
            //$this->$menuItems = $menuItems;
            $this->basePath = $basePath;
        }

        public function addMenuItem(bool $isLeft, MenuItem $menuItem) {
            if ($isLeft) {
                $this->menuItemsLeft[] = $menuItem;
            }
            else {
                $this->menuItemsRight[] = $menuItem;
            }
        }

        public function display() {
            //html header and start of menu bar:
            echo("
            <!DOCTYPE html>
            <html lang='en'>
                <head>
                    <meta charset='utf-8'>
                    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                    <meta name='viewport' content='width=device-width, initial-scale=1'>
                    <title>".$this->pageTitle."></title>
                    <!-- CSS from bootsrap -->
                    <link rel='stylesheet' href='".$this->basePath."bootstrap/css/bootstrap.min.css'>
                </head>
                <body>
                <nav class='navbar navbar-inverse navbar-fixed-top'>
                    <div class='container'>
                        <div class='navbar-header'>
                            <button class='navbar-toggle' type='button' data-toggle='collapse' data-target='#navbar'>
                            <span class='icon-bar'></span>
                            <span class='icon-bar'></span>
                            <span class='icon-bar'></span>
                            </button>
                            <a class='navbar-brand' href='#'>Arma 3 League</a>
                        </div>
                ");
            //menu left:
            echo("<div id='navbar' class='collapse navbar-collapse'>
                        <ul class='nav navbar-nav navbar-left'>");
            foreach($this->menuItemsLeft as $item) {
                echo("<li>".$item.display()."</li>");
            }
            echo("</ul>");
            //menu right:
            echo("<ul class='nav navbar-nav navbar-right'>
                <ul>");
            foreach($this->menuItemsRight as $item) {
                echo("<li>".$item.display()."</li>");
            }
            echo("</ul></div></div></nav>");
        }
    }

    //TODO: Menu Forms
    class MenuItem {
        private $href;
        private $text;

        public function __construct(String $href, String $text) {
            $this->href = $href;
            $this->text = $text;
        }

        public function display() {
            return "<a href='".$this->href."'>".$this->text."</a>";
        }
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
            $message = "You are now logged out.";
            unset($_SESSION['user']);
            session_destroy();
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
                    <li><a href="squadPage.php">My Squad</a>
                    </li>
                </ul>
                <?php
                    if (!isset($_SESSION['user'])) {
                ?>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="register.php"><b>Not a user yet? Register</b></a>
                    </li>
                </ul>
                    <form class="navbar-form navbar-right" method="POST">
                        <div class="form-group">
                            <input type="text" class="form-control" name="username" placeholder="Username">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="Password">
                        </div>
                        <button type="submit" class="btn btn-default">Sign In</button>
                    </form>
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

            ?>