<?php

    if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
    }
    ini_set("display_errors", 1);
    
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
        private $credits;
        private $error = "";
        private $message = "";

        /*
        constructor can take an $_POST-array directly and does all the necessary validations
        if a validation fails, the constructor will throw
        Errors in non-mandatory field do not throw, but are written into $this->error

        TODO: Differentiate error-messages ("Your username is not valid" for squad mates)
        TODO: Clan Image
        TODO: FILTER_VALIDATE_URL seems not very bright
        TODO: Completly forgot the status attribute ...
        */
        public function __construct($array) {
           
            if (!isset($array['name']) || !isset($array['leadername']) || !isset($array['players'])) {
                throw new InvalidArgumentException("Fill out all required form elements");
            }
            try {
                //class Name will validate these fields:
                $this->name = new Name($array['name']);
                $this->leader = new Name($array['leadername']);

                //TODO: Error prone. What if a player has a ',' in his name ?? We need to check player names !
                $playersRaw = explode(",", $array['players']);
                
                //make sure a player was not registered twice or more. We give no error if this happend.
                //TODO: DOES NOT WORK PROPERLY
                for($i = 0; $i < count($playersRaw); $i++) {
                    trim($playersRaw[$i]);
                }
                $players = array_unique($playersRaw);

                if (count($players) < 2) {
                    throw new InvalidArgumentException("You need a leader and at least two players to form a squad. You only gave us ".count($players)." Players.");
                }
                foreach($players as $player) {
                    $this->players[] = new Name($player);
                }

                //check for voluntary fields:
                if (isset($array['url'])) {
                    if (filter_var(htmlspecialchars(trim($array['url']), FILTER_VALIDATE_URL))) {
                        $this->url = htmlspecialchars(trim($array['url']));
                    }
                    else $this->error .= "Your clan url seems invalid.";
                }
                //TODO: Clan image
                
                //The following variables can not be set by a normal user, we skip validations here:
                $this->side = isset($array['side']) ? $array['side'] : null;
                $this->credits = isset($array['credits']) ? $array['credits'] : "0";
            }
            //TODO: Remove
            catch (InvalidArgumentException $e) {
                throw $e;
            }
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

            //the caller of this method is also interested in the players belonging to this squad, so fetch them:
            //TODO: This seems a duplication if the code at the start if this function
            $query = "SELECT username, id FROM Player WHERE squadID = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $squadID);
            $stm->execute();
            $result = $stm->fetch(PDO::FETCH_ASSOC);

            //constructor wants comma-separated string with players (so we put the array in a string here
            //and the constructor makes an array from this string again ....)
            $playernames = "";
            $leadername = "";
            foreach($result as $r) {
                if ($r['id'] == $leader) {
                    $leadername = $r['username'];
                }
                else {
                    $playernames .= $r['username'].",";
                }
            }
            $playernames = substr($playernames, 0, -1);

            //finally, construct a squad object:
            $squad = new Squad(array(
                ['name'] => $name, 
                ['leadername'] => $leadername,
                ['players'] => $playernames,
                ['side'] => $side,
                ['img'] => $img,
                ['url'] => $url
            ));
            return Squad($squad);
        }

        /* 
        save a new squad in the database 
        needs a PDO-Connection as parameter
        - new squads will always have 0 credits
        */
        public function save(PDO $connection) {
            //ERROR CHECK: Is one of the players already in a squad ?
            $squadIDs = fetchSquadID($connection, $this->players);
            foreach($squadIDs as $id) {
                if (!is_null($id)) {
                    throw new InvalidArgumentException("One of your player already is in a squad");
                }
            }   
            //TODO: Include squad-leader in above test

            //ERROR-CHECK: Does a squad with this name already exists?
            $query = "SELECT name FROM Squad WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(':name', $this->name);
            $stm->execute();
            //If the name exists, give an error:
            if ($stm->rowCount() != 0) {
                throw new InvalidArgumentException("A squad with this name already exists.");
            }

            //ERROR CHECKS DONE => WRITE SQUAD TO DATABASE
            $leaderID = $this->fetchAttribute($connection, "id");
            echo("<h1>".$leaderID."</h1>");

            $query = "INSERT INTO Squad (name, image, url, sideID, leaderID, credits)
                    VALUES (:name, :image, :url, :side, :leader, :credits)";
            $stm = $connection->prepare($query);
            echo("<h1>".$query."</h1>");

            $success = $stm->execute(array(
                ':name' => $this->name,
                ':url' => $this->url,
                ':image' => $this->image,
                ':leader' => $leaderID,
                ':side' => $this->side,
                ':credits' => $this->credits 
            ));

            if (!$success) {
                echo("<h1>".$success."</h1>");
                throw new InvalidArgumentException("11 We seem to have a problem with our database. 
                    Please try again later or contact an admin."); 
            }

            //SQUAD CREATED -> UPDATE PLAYER TABLE
            $squadID = fetchAttribute($connection, "id");
            $allPlayers = $players;
            $allPlayers.append($this->$leader);

            //squadID got just fetched from the database, we can omit prepared statement for it
            $in = str_repeat("?,", count($allPlayers)-1) . "?";
            $stmt = $connection->prepare("UPDATE Player SET squadID = ".$squadID." WHERE username IN ($in)");
            $success = $stm->execute($allPlayers);

            if (!$success) {
                throw new InvalidArgumentException("We seem to have a problem with our database. 
                    Please try again later or contact an admin."); 
            }
        }

        //private function: fetch a given attribute from the database
        private function fetchAttribute(PDO $connection, String $attrib) {
            $query = "SELECT ".$attrib." FROM Player WHERE username = :name";
            echo("<h1>".$query."</h1>");
            $stm = $connection->prepare($query);
            echo("<h1>".$this->leader."</h1>");
            $stm->bindParam(":name", $this->leader);
            $success = $stm->execute();
            if (!$success) {
                throw new InvalidArgumentException("22 We seem to have a problem with our database. 
                    Please try again later or contact an admin."); 
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            return $result[$attrib];
        }
    }

?>