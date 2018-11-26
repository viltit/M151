<?php

    if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
    }
    
    /*  custom exceptions for proper error handling 
        no custom behaviour needed, we just want to catch the proper exception and react accordingly
    */
    class NoSquadException extends Exception {       
        public function __construct($message, $code = 0, Exception $previous = null) {
            parent::__construct($message, $code, $previous);
        }
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
        private $players;
        private $side;
        private $error;
        private $message;

        /*
        constructor takes a database connection and a squadname
        */
        public function __construct(String $name, String $img, String $url, int $leader, int $side) {
            $this->name = $name;
            $this->url = $url;
            $this->img = $img;
            $this->leader = $leader;
            $this->players = array();
            $this->side = $side;
            $this->error = "";
            $this->message = "";
        }

        /* 
        static function for loading a squad for a given player name
        returns a new squad Instance on success
        throws an error if the given player is in no suqad
        */
        public static function load(PDO $connection, String $username) {
            //first, check if the given player has a squad id
            $query = "SELECT squadID FROM Player WHERE username = :username";
            $stm = $connection->prepare($query);
            $stm->bindParam(":username", $username);
            $stm->execute();

            //row count is zero? This username does not exist. 
            //This should not happen if we programm carefully, but we check to be sure
            if ($stm->rowCount() == 0) {
               throw new InvalidArgumentException("A player with the given name does not exist");
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            if (is_null($result['squadID'])) {
                throw new NoSquadException("Player has no squad");
            }
            $squadID = $result['squadID'];
            //we have the squadID -> Fetch all information about this squad:
            $query = "SELECT * FROM Squad WHERE id = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $squadID);
            $stm->execute();

            //again, if we programm carefully, we always have a result, but we check to make sure:
            if ($stm->rowCount() == 0) {
                throw new InvalidArgumentException("A squad with the given id does not exist! (Bad, bad programming...)");
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            $name = $result['name'];
            $url = is_null($result['url']) ? "" : $result['url'];
            $img = is_null($result['image']) ? "" : $result['image'];
            $leader = $result['leaderID'];
            $side = is_null($result['sideID']) ? -1 : $result['sideID'];

            //finally, construct a squad object:
            $squad = new Squad($name, $img, $url, $leader, $side);

            //the caller of this method is also interested in the players belonging to this squad, so fetch them:
            //TODO: This seems a duplication if the code at the start if this function
            $query = "SELECT * FROM Player WHERE squadID = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $squadID);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);

            return array(
                'squad' => $squad,
                'players' => $result
            );
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