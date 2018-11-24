<?php

    if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
    }
    
    /* This is how the squad look like in the database:

    +-------------+---------------------------+------+-----+---------+----------------+
    | Field       | Type                      | Null | Key | Default | Extra          |
    +-------------+---------------------------+------+-----+---------+----------------+
    | id          | int(11)                   | NO   | PRI | NULL    | auto_increment |
    | name        | varchar(20)               | NO   |     | NULL    |                |
    | image       | varchar(20)               | YES  |     | NULL    |                |
    | url         | varchar(20)               | YES  |     | NULL    |                |
    | credits     | int(11)                   | NO   |     | NULL    |                |
    | sideID      | int(11)                   | YES  | MUL | NULL    |                |
    | inventoryID | int(10) unsigned zerofill | YES  |     | NULL    |                |
    | leaderID    | int(11)                   | NO   | MUL | NULL    |                |
    +-------------+---------------------------+------+-----+---------+----------------+
    */

    class Squad {
        private $name;
        private $url;
        private $image;
        private $leader;
        private $side;
        private $error;
        private $message;

        /*
        constructor takes a database connection and a squadname
        */
        public function __construct(PDO $connection, String $name, String $img, String $url, int $leader, int $side) {
        
        }

        /* 
        save a new squad in the database 
        needs a PDO-Connection as parameter
        - new squads will always have 0 credits
        */
        public function save(PDO $connection) {
            //check if a squad with the given name already exists:
            $query = "SELECT name FROM Squad WHERE name = :name";
            $stm = $connection->prepare($query);
            //TODO: Error check;
            $stm->bindParam(':name', $this->name);
            $stm->execute();
            //If the name, give an error:
            if ($stm->rowCount() != 0) {
                $this->error = "A squad with this name already exists.";
            }
            //if not, write to database:
            else {
                $query = "INSERT INTO Squad (name, image, url, sideID, leaderID)
                        VALUES (:name, :image, :url, :side, :leader)";
                $stm = $connection->prepare($query);
                //we bind on execution:
                $success = $stm->execute(array(
                    ':name' => $this->name,
                    ':firstName' => $this->image,
                    ':email' => $this->url,
                    ':username' => $this->side,
                    ':password' => $this->leader
                ));
                if (!$success) {
                   $this->error = "We seem to have a problem with our database. Please try again later or contact an admin."; 
                }
                else {
                    
                }
            }            
        }

    }


?>