<?php

    /*
    Class to represent a squads inventory
    This class does NOT model the Database Table "Inventory", but rather the combination of the 
    tables Inventory, InventoryItem and ItemType
    */

    require_once($basePath."validations/itemType.php");

    class Inventory {
        private $items = array();
        private $ingameItems = array(); //TODO: fix ad hoc solution
        private $squadID;
        private $id;
        private $status;

        /*
        in contrast to other classes, this one constructs itself from the database. Constructor needs
        the name of the Squad this inventory belongs to
        */
        public function __construct(PDO $connection, String $squad) {
            
            $query = "SELECT Inventory.status, ItemType.name AS name, ItemClass.name AS class,
                        Squad.id AS squadID, Inventory.id AS id, InventoryItem.status AS itemStatus
                        FROM Inventory
                        INNER JOIN Squad ON Inventory.id = Squad.inventoryID
                        LEFT OUTER JOIN InventoryItem ON Inventory.id = InventoryItem.inventoryID
                        LEFT OUTER JOIN ItemType ON InventoryItem.typeID = ItemType.id
                        LEFT OUTER JOIN ItemClass ON ItemType.classID = ItemClass.id
                        WHERE Squad.name = :squad";

            $stm = $connection->prepare($query);
            $stm->bindParam(":squad", $squad);
            if (!$stm->execute()) {
                throw InvalidArgumentException("Database could not load inventory");
            }
            
            while ($result = $stm->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($this->id)) {
                    $this->id = $result['id'];
                    $this->squadID = $result['squadID'];
                    $this->status = $result['status'];
                }
                //because the database saves each item and I did not get how to count them with an left outer join, we need to count them here
                //we also need to differentiate the status of the item
                if (isset($result['name']) && $result['itemStatus'] == 'inStore') {
                    $count = isset($this->items[$result['name']]) ? $this->items[$result['name']] : 0;
                    $this->items[$result['name']] = $count + 1;
                }
                else if (isset($result['name']) && $result['itemStatus'] == 'inGame') {
                    $count = isset($this->ingameItems[$result['name']]) ? $this->ingameItems[$result['name']] : 0;
                    $this->ingameItems[$result['name']] = $count + 1;
                }
            }
            print_r($this);
        }

        //getters:
        public function status() {
            return $this->status;
        }
        public function items() {
            return $this->items;
        }
        public function ingameItems() {
            return $this->ingameItems;
        }

        /*
         creates a new Inventory for a squad with a given name
         */
        public static function create(PDO $connection, String $squad) {
        
            //get squads id:
            $query = "SELECT id FROM Squad WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $squad);
            $stm->execute();
            if ($stm->rowCount() != 1) {
                throw InvalidArgumentException("Could not find a squad named ".$squad);
            }
            $id = $stm->fetch(PDO::FETCH_ASSOC)['id'];

            //create a new Inventory in the database:
            $query = "INSERT INTO Inventory (squadID, status) VALUES (:id, 'open')";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $id);
            $stm->execute();
            
            //TODO: Can we just fetch the new id?
            $iid = $connection->lastInsertId();
        
            //link the new Inventory to out squad
            $query = "UPDATE Squad SET inventoryID = :iid WHERE id = :id";
            $stm2 = $connection->prepare($query);
            if (!$stm2->execute(array(
                ':iid' => $iid,
                ':id' => $id
            ))) {
               throw new InvalidArgumentException("Database could not create a new Inventory."); 
            }
        }

        /*
        remove an item from this inventory
        */
        public function remove(PDO $connection, String $name) {
            $id = ItemType::getID($connection, $name);
            $query = "SELECT id FROM InventoryItem WHERE typeID = :typeID 
                AND inventoryID = :inventoryID
                AND status = 'inStore'";
            $stm = $connection->prepare($query);
            $stm->execute(array(
                ":typeID" => $id,
                ":inventoryID" => $this->id
            ));
            if ($stm->rowCount() == 0) {
                throw new InvalidArgumentException("Can not remove item ".$name." from Inventory because it does not exist.");
            }
            //just remove the first entry:
            $result = $stm->fetch(PDO::FETCH_ASSOC)['id'];
            $query = "DELETE FROM InventoryItem WHERE id = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $result);
            $stm->execute();

            //finally, adjust self:
            $this->items[$name] -= 1;
        }

        /*
        add an item to this inventory and saves it in the database
        - parameter $name: The name of the item to add
        - TODO: Work with the items id to prevent unnecessary database access
        */
        public function add(PDO $connection, String $name) {
            //get the id of this item
            $query = "SELECT id FROM ItemType WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $name);
            $stm->execute();
            $id = $stm->fetch(PDO::FETCH_ASSOC)['id'];

            //create a new inventory item that references to this squad and this itemType
            $query = "INSERT INTO InventoryItem (inventoryID, typeID, status) VALUES (:inventoryID, :typeID, 'inStore')";
            $stm = $connection->prepare($query);
            if (!$stm->execute(array(
                ":inventoryID" => $this->id,
                ":typeID" => $id
            ))) {
                throw new InvalidArgumentException("Database refused purchase!");
            }

            //update self:
            if (isset($this->items[$name])) {
                $this->items[$name] += 1;
            }
            else {
                $this->items[$name] = 1;
            }
        }

        /* 
            set an inventory item to status 'inGame' 
            - parameter toGame: if true, the item will be tagged as 'inGame', if false as 'inCamp'
        */
        public function toGame(PDO $connection, String $name, Bool $toGame) {
            $id = ItemType::getID($connection, $name);
            $nowStatus = $toGame ? 'inStore' : 'inGame';
            $futureStatus = $toGame? 'inGame' : 'inStore';
            $query = "SELECT id FROM InventoryItem WHERE typeID = :typeID 
                AND inventoryID = :inventoryID
                AND status = :status";
            $stm = $connection->prepare($query);
            $stm->execute(array(
                ":typeID" => $id,
                ":inventoryID" => $this->id,
                ":status" => $nowStatus
            ));
            if ($stm->rowCount() == 0) {
                throw new InvalidArgumentException("Can not move item ".$name." to game because it does not exist.");
            }
   
            $result = $stm->fetch(PDO::FETCH_ASSOC)['id'];
            $query = "UPDATE InventoryItem SET status = :status WHERE id = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $result);
            $stm->bindParam(":status", $futureStatus);
            $stm->execute();

            //finally, adjust self:
            if ($toGame) {
                $this->items[$name] -= 1;
                $this->ingameItems[$name] += 1;
            }
            else {
                $this->items[$name] += 1;
                $this->ingameItems[$name] -= 1;
            }
        }
    }
?>