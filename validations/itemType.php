<?php
    
    ini_set("display_errors", 1);

    require_once($basePath."validations/image.php");
    require_once($basePath."validations/name.php");
    require_once($basePath."validations/side.php");

    /*
    This class represents an Item that can be bought, selled or taken into combat by the squads.
     */
    class ItemType {
        private $name;          //the name displayed in the web-app
        private $ingameName;    //the name used in game-scripts
        private $img;           //image path
        private $price;
        private $side;
        private $class;

        /* 
        Constructor makes a new ItemType directly from a $_POST-array and performs all necessary validation
        - parameter array: The $_POST-array
        - parameter imageTempName: Temporary image name used in $_FILES[$imageTempName]
        - parameter basePath: The path from the caller to the web-root path
        */
        public function __construct($array, $imageTempName, $basePath) {
            if (!isset($array['name']) || !isset($array['ingameName']) || !isset($array['price'])
                    || !isset($array['side']) || !isset($array['class'])) {
                        throw new InvalidArgumentException("<InventoryItem::InventoryItem> Missing parameters.");
                    }
            $this->name = new Name($array['name']);
            $this->ingameName = new Name($array['ingameName']);
            
            //image
            if (isset($array['image'])) {
                $this->img = $array['image'];
            }
            //no image path delivered -> upload an image
            else {
                if (empty($imageTempName)) {
                    throw new InvalidArgumentException("<InventoryItem::InventoryItem> Missing image");
                }
                $image = new ImageUploader($imageTempName, $basePath."images/inventory/".$this->name);
                $this->img = $this->name;      //TODO: Redundant information
            }

            if (!is_numeric($array['price'])) {
                throw new InvalidArgumentException("<InventoryItem::InventoryItem> Argument 'price' is not numeric.");
            }

            //TODO: Validate ??
            $this->price = $array['price'];
            $this->side = $array['side'];
            $this->class = $array['class'];
        }

        public function save(PDO $connection) {
            //TODO IMPORTANT: Check if item already exists !

            //fetch sideID from the database
            $query = "SELECT id FROM Side WHERE NAME = :name";
            $stm = $connection->prepare($query);
            if ($this->side != "all") {
                $stm->bindParam(":name", $this->side);
                $stm->execute();
                if ($stm->rowCount() != 1) {
                    throw new InvalidArgumentException("<ItemType::save> Could not find side id for ".$this->side);
                }
                $result = $stm->fetch(PDO::FETCH_ASSOC);
                $sideID = $result['id'];
            }
            else {
                $sideID = null;
            }

            //fetch class id:
            $query = "SELECT id FROM ItemClass WHERE NAME = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $this->class);
            $stm->execute();
            if ($stm->rowCount() != 1) {
                throw new InvalidArgumentException("<ItemType::save> Could not find class id for ".$this->class);
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            $classID = $result['id'];

            //finally, we are ready to save the Item:
            $query = "INSERT INTO ItemType (name, image, price, scriptName, classID, sideID)
                        VALUES (:name, :image, :price, :scriptName, :classID, :sideID)";
            $stm = $connection->prepare($query);
            $result = $stm->execute(array(
                ":name" => $this->name,
                ":image" => $this->name,
                ":price" => $this->price,
                ":scriptName" => $this->ingameName,
                ":classID" => $classID,
                ":sideID" => $sideID
            ));
            if (!$result) {
                throw new InvalidArgumentException("<ItemType::save> Database returned false while saving!");
            }
        }

        /*
        Load all ItemTypes from the database.
        - parameter forSide: optional, if user only wants to load inventory for one side
        */
        public function loadAll(PDO $connection, String $orderBy = null, String $forSide = null) {
            echo("<h1>".$orderBy."</h1>");
            $query = "SELECT ItemType.name, image, price, scriptName, ItemClass.name AS Class, Side.name AS side 
                      FROM ItemType 
                      LEFT JOIN Side ON ItemType.sideID = Side.id 
                      LEFT OUTER JOIN ItemClass ON ItemType.classID = ItemClass.id";

            if ($forSide != null) {
                //TODO: This does no include side = null
                $query .= " WHERE Side.name = '".$forSide."'";
            }
            //TODO: Solve this with associative array ? ($query .= orders[$order])
            if ($orderBy != null) {
                if ($orderBy == "bySide") {
                    $query .= " ORDER BY Side.name";
                }
                else if ($orderBy == "byPrice") {
                    $query .= " ORDER BY price";
                }
                else if ($orderBy == "byName") {
                    $query .= " ORDER BY name";
                }
                else if ($orderBy == "byClass") {
                    $query .= " ORDER BY Class";
                }
            }
            print($query);

            $stm = $connection->prepare($query);
            
            if (!$stm->execute()) {
                throw new InvalidArgumentException("Database query returned false.");
            }

            //TODO: Implement selecting only one side
            //TODO: Implement order by

            $items = array();
            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $side = "";
                if (!isset($row['side'])) {
                    $side = "all";
                }
                else {
                    $side = $row['side'];
                }
                $items[] = new ItemType(array(
                    'name' => $row['name'],
                    'ingameName' => $row['scriptName'],
                    'image' => $row['image'],
                    'price' => $row['price'],
                    'side' => $side,
                    'class' => $row['Class']
                ), "", "");
            }
            return $items;
        }

        //getters:
        public function name() {
            return $this->name;
        }
        public function scriptName() {
            return $this->ingameName;
        }
        public function image() {
            return $this->img;
        }
        public function price() {
            return $this->price;
        }
        public function side() {
            return $this->side;
        }
        public function class() {
            return $this->class;
        }

    }

?> 