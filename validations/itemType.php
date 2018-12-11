<?php
    
    ini_set("display_errors", 1);

    require_once($basePath."validations/image.php");

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
        */
        public function __construct($array, $imageURL) {
            if (!isset($array['name']) || !isset($array['ingameName']) || !isset($array['price'])
                    || !isset($array['side']) || !isset($array['class'])) {
                        throw new InvalidArgumentException("<InventoryItem::InventoryItem> Missing parameters.");
                    }
            $this->name = new Name($array['name']);
            $this->ingameName = new Name($array['ingameName']);
            //TODO: IMAGE !!
            $this->img = $imageURL;
            if (!is_numeric($array['price'])) {
                throw new InvalidArgumentException("<InventoryItem::InventoryItem> Argument 'price' is not numeric.");
            }

            //TODO: Validate ??
            $this->price = $array['price'];
            $this->side = $array['side'];
            $this->class = $array['class'];
        }

        public function save(PDO $connection) {
            //fetch sideID from the database
            $query = "SELECT id FROM Side WHERE NAME = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $this->side);
            $stm->execute();
            if ($stm->rowCount() != 1) {
                throw new InvalidArgumentException("<ItemType::save> Could not find side id for ".$this->side);
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            $sideID = $result['id'];

            //fetch class id:
            $query = "SELECT id FROM ItemClass WHERE NAME = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $this->class);
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
                ":image" => $this->img,
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
        public function loadAll(String $forSide) {
            //todo
        }
    }

?>