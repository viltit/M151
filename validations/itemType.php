<?php
    
    ini_set("display_errors", 1);

    require_once($basePath."validations/image.php");
    require_once($basePath."validations/name.php");
    require_once($basePath."validations/side.php");
    require_once($basePath."validations/itemClass.php");

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

        //update an existing entry. Needs a valid script name
        public function update(PDO $connection, String $scriptName) {
            
            //this code gets repeated in function save() ....
            if ($this->side != "all") {
                $sideID = Side::getIDFromName($connection, $this->side);
            }
            else {
                $sideID = null;
            }
            $classID = ItemClass::getIDFromName($connection, $this->class);

            $query = "UPDATE ItemType SET name = :name,
                            scriptName = :scriptName,
                            image = :image,
                            classID = :classID,
                            sideID = :sideID,
                            price = :price
                        WHERE scriptName = :oldScriptName";
            
            $stm = $connection->prepare($query);
            $stm->execute(array(
                ":name" => $this->name,
                ":image" => $this->name,
                ":price" => $this->price,
                ":scriptName" => $this->ingameName,
                ":classID" => $classID,
                ":sideID" => $sideID,
                ":oldScriptName" => $scriptName
            ));

            if (!$stm->execute()) {
                throw new InvalidArgumentException("Updating item named ".$this->name." in the database failed!");
            }
        }

        public function save(PDO $connection) {
            //TODO IMPORTANT: Check if item already exists !
            
            //fetch sideID and classID from the database
            $query = "SELECT id FROM Side WHERE NAME = :name";
            $stm = $connection->prepare($query);
            if ($this->side != "all") {
                $sideID = Side::getIDFromName($connection, $this->side);
            }
            else {
                $sideID = null;
            }
            $classID = ItemClass::getIDFromName($connection, $this->class);

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
        public static function loadAll(PDO $connection, String $orderBy = null, String $forSide = null) {
            $query = "SELECT ItemType.name, image, price, scriptName, 
                      ItemClass.name AS Class, Side.name AS side, Side.id AS sideID 
                      FROM ItemType 
                      LEFT JOIN Side ON ItemType.sideID = Side.id 
                      LEFT OUTER JOIN ItemClass ON ItemType.classID = ItemClass.id";

            if ($forSide != null) {
                //TODO: This does no include side = null. For now, I just include it
                $query .= " WHERE Side.name = '".$forSide."' OR Side.id is null";
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
                else if ($orderBy == "byScript") {
                    $query .= " ORDER BY scriptName";
                }
            }

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