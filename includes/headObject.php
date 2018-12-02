
    <?php
    
    //This is a first a try in making the head-menu an object.
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
            $menuItem = new MenuItem($href, $text);
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
                echo("<li class='nav-item active'>".$item->display()."</li>");
            }
            echo("</ul>");
            //menu right:
            echo("<ul class='navbar-nav mr-sm-2'>");
            foreach($this->menuItemsRight as $item) {
                echo("<li>".$item.display()."</li>");
            }
            echo("</ul></div></div></nav>");
            echo("<div class='container' style='padding-top: 40px;'>");
        }
    }

    //Menu Items: Text and link
    class MenuItem {
        private $href;
        private $text;

        public function __construct(String $href, String $text) {
            $this->href = $href;
            $this->text = $text;
        }

        public function display() {
            return "<a class='nav-link' href='".$this->href."'>".$this->text."<span class='sr-only'>(current)</span></a>";
        }
    }

?>