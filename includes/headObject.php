
    <?php
    
    /*
    This is my first try to make the sites header an object and more flexible
    TODO: add some kind of position index for more flexibility (-> dropdown between two normal menu items)
    TODO: (for future use) Function to add stylesheets and scripts
    */

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

        public function addMenuItem(bool $isLeft, String $text, String $href) {
            $menuItem = new MenuItemLink($href, $text);
            if ($isLeft) {
                $this->menuItemsLeft[] = $menuItem;
            }
            else {
                $this->menuItemsRight[] = $menuItem;
            }
        }

        public function addDropdown(String $title, $hrefs) {
            $this->menuItemsLeft[] = new MenuItemDropdown($title, $hrefs);
        }

        public function addForm($types, $names, $placeholders) {
            $this->menuItemsRight[] = new MenuItemForm($types, $names, $placeholders);
        }

        public function display() {
            //html header, including of stylesheets etc. and start of menu bar:
            echo("
            <!DOCTYPE html>
            <html lang='en'>
                <head>
                    <meta charset='utf-8'>
                    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                    <meta name='viewport' content='width=device-width, initial-scale=1'>
                    <title>".$this->pageTitle."</title>
                    <!-- JS and CSS from bootsrap. JS seems to be needed for dropdown menu -->
                    <script src='https://code.jquery.com/jquery-3.2.1.slim.min.js' integrity='sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN' crossorigin='anonymous'></script>
                    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js' integrity='sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q' crossorigin='anonymous'></script>
                    <link rel='stylesheet' href='".$this->basePath."bootstrap/css/bootstrap.min.css'>
                    <script src='".$this->basePath."bootstrap/js/bootstrap.min.js'></script>
                </head>
                <body>
                    <nav class='navbar navbar-dark bg-dark navbar-fixed-top navbar-expand-lg'>
                        <a class='navbar-brand' href='#'><img src='".$this->basePath."images/arma3Logo.png' class='img-fluid'></a>
                        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
                        <span class='navbar-toggler-icon'></span>
                        </button>
                ");
            //menu items on left side:
            echo("
                        <div class='collapse navbar-collapse' id='navbarSupportedContent'>
                            <ul class='navbar-nav mr-auto'>");
            foreach($this->menuItemsLeft as $item) {
                echo($item->display());
            }
            echo("</ul>");

            //menu right:
            echo("<ul class='navbar-nav mr-sm-2'>");
            foreach($this->menuItemsRight as $item) {
                echo("<li>".$item->display()."</li>");
            }
            echo("</ul></div></div></nav>");
            echo("<div class='container' style='padding-top: 40px;'>");
        }
    }

    //Iterface for menu items:
    interface MenuItem {
        public function display() : String;
    }

    //Menu Items: Text and link
    class MenuItemLink implements MenuItem {
        private $href;
        private $text;

        public function __construct(String $href, String $text) {
            $this->href = $href;
            $this->text = $text;
        }

        public function display() : String {
            return "<li class='nav-item active'>
                    <a class='nav-link' href='".$this->href."'>".$this->text."<span class='sr-only'>(current)</span></a>
                    </li>";
        }
    }

    class MenuItemForm implements MenuItem {

        private $types;
        private $names;
        private $placeholders;

        public function __construct($types, $names, $placeholders) {
            if (count($types) != count($placeholders) - 1 || count($types) != count($names) - 1) {
                throw new InvalidArgumentException("MenuItemForm: Types, names and placeholders have different counts.");
            }
            $this->types = $types;
            $this->names = $names;
            $this->placeholders = $placeholders;
        }

        public function display() : String {
            $result = "<form class='form-inline my-2 my-lg-0' method='POST'>";
            for ($i = 0; $i < count($this->types); $i++) {
                $result .= "<input type='".$this->types[$i]."' class='form-control mr-sm-2' name='".$this->names[$i]."' placeholder='".$this->placeholders[$i]."' required>";
            }
            $result .=  "<button type='submit' name='".end($this->names)."' class='btn btn-default'>".end($this->placeholders)."</button>";
            $result .= "</form>";
            return $result;
        }
    }

    class MenuItemDropdown implements MenuItem {
        private $title;
        private $hrefs = array();

        public function __construct(String $title, $hrefs) {
            print_r($hrefs);
            $this->title = $title;
            $this->hrefs = $hrefs;
        }

        public function display() : String {
            $result = "
                <li class='nav-item dropdown'>
                    <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' 
                        data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        ".$this->title."
                    </a>
                <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                ";
            foreach($this->hrefs as $title => $href) {
                $result .= "<a class='dropdown-item' href='".$href."'>".$title."</a>";
            }
            $result .= "</div></li>";
            return $result;
        }
    }

?>