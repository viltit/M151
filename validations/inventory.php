<?php

    /*
    Class to represent a squads inventory
    This class does NOT model the Database Table "Inventory", but rather the combination of the 
    tables Inventory, InventoryItem and ItemType
    */

    class Inventory {
        private $items = [];
        private $status;

        /*
        in contrast to other classes, this one constructs itself from the database. Constructor needs
        the name of the Squad this inventory belongs to
        */
        public function __construct(PDO $connection, String $squad) {
            
            $query = "SELECT Inventory.status, ItemType.name AS name, ItemClass.name AS class
                        FROM Inventory
                        INNER JOIN Squad ON Inventory.id = Squad.inventoryID
                        INNER JOIN InventoryItem ON Inventory.id = InventoryItem.inventoryID
                        INNER JOIN ItemType ON InventoryItem.typeID = ItemType.id
                        INNER JOIN ItemClass ON ItemType.classID = ItemClass.id
                        WHERE Squad.name = :squad";

            $stm = $connection->prepare($query);
            $stm->bindParam(":squad", $squad);
            if (!$stm->execute()) {
                throw InvalidArgumentException("Database could not load inventory");
            }
            

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
    }
?>