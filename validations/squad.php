<?php

    if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
    }
    ini_set("display_errors", 1);
    require_once($basePath."validations/name.php");

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
        TODO: Completly forgot the status attribute ... somehow works without.
        TODO: When a database operation fails after we already changed somethin, we should revert all changes!
        TODO: Custom exception classes for better error handling
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
                $this->side = isset($array['side']) ? $array['side'] : "pending";
                $this->credits = isset($array['credits']) ? $array['credits'] : "0";
                $this->status = isset($array['status']) ? $array['status'] : "pending";
            }
            //TODO: Remove
            catch (InvalidArgumentException $e) {
                throw $e;
            }
        }

        //getters:
        public function getName() {
            return $this->name;
        }
        public function getLeader() {
            return $this->leader;
        }
        public function getPlayers() {
            return $this->players;
        }
        public function getPlayersPrettyString() {
            return implode (", ", $this->players);
        }
        public function getCredits() {
            return $this->credits;
        }
        public function getSide() {
            return $this->side;
        }
        public function getURL() {
            return $this->url;
        }
        public function getImage() {
            return $this->image;
        }
        public function getStatus() {
            return $this->status;
        }

        /* 
        static function for loading a squad for a given player name
        returns a new squad Instance on success
        throws an error if the given player is in no suqad

        TODO: This could be signficantly more elegant with a JOIN-Query
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
            //TODO: Leave nulls be null 
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            $name = $result['name'];
            $url = $result['url'];
            $img = $result['image'];
            $leader = $result['leaderID'];
            $side = $result['sideID'];
            $status = $result['status'];

            //the caller of this method is also interested in the players belonging to this squad, so fetch them:
            //TODO: This seems a duplication if the code at the start if this function
            $query = "SELECT username, id FROM Player WHERE squadID = :id";
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $squadID);
            $stm->execute();
            //$result = $stm->fetch(PDO::FETCH_ASSOC);

            //constructor wants comma-separated string with players (so we put the array in a string here
            //and the constructor makes an array from this string again ....)
            $playernames = "";
            $leadername = "";
            while($result = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($result['id'] == $leader) {
                    $leadername = $result['username'];
                }
                else {
                    $playernames .= $result['username'].",";
                }
            }
            $playernames = substr($playernames, 0, -1);

            //finally, construct a squad object:
            $squad = new Squad(array(
                'name' => $name, 
                'leadername' => $leadername,
                'players' => $playernames,
                'side' => $side,
                'img' => $img,
                'url' => $url,
                'status' => $status
            ));
            return $squad;
        }

        /*
        Load ALL Squads in the database
        Returns an array with the Squads
        */
        public static function loadAll(PDO $connection) {
            $query = "SELECT * FROM Squad ";
            $result = $connection->query($query);
            $squads = array();
            //we will need the player names:
            $query = "SELECT username, id FROM Player WHERE squadID = :squadID";
            $stm = $connection->prepare($query);
            
            while ($squad = $result->fetch(PDO::FETCH_ASSOC)) {
                $stm->bindParam(":squadID", $squad['id']);
                $stm->execute();
                $playernames = "";
                $leadername = "";
                while($player = $stm->fetch(PDO::FETCH_ASSOC)) {
                    if ($player['id'] == $squad['leaderID']) {
                        $leadername = $player['username'];
                    }
                    else {
                        $playernames .= $player['username'].",";
                    }
                }
                $playernames = substr($playernames, 0, -1);
                $squads[] = new Squad(array(
                    'name' => $squad['name'], 
                    'leadername' => $leadername,
                    'players' => $playernames,
                    'side' => $squad['sideID'],
                    'img' => $squad['image'],
                    'url' => $squad['url'],
                    'status' => $squad['status']
                ));
            }
            return $squads;
        }

        /* 
        save a new squad in the database 
        needs a PDO-Connection as parameter
        - new squads will always have 0 credits
        */
        public function save(PDO $connection) {
            //ERROR CHECK: Is one of the players already in a squad ?
            $squadIDs = $this->isInForeignSquad($connection, $this->players);
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
            $leaderID = $this->getLeaderID($connection);
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
                throw new InvalidArgumentException("We seem to have a problem with our database (Could not save squad). 
                    Please try again later or contact an admin."); 
            }

            //SQUAD CREATED -> UPDATE PLAYER TABLE
            $squadID = $connection->lastInsertId();
            echo("<h1> SquadID: ".$squadID);
            //the squadID was just fetched from the database, no need to prepare
            $query = "UPDATE Player SET squadID = ".$squadID." WHERE username = :username";
            $stm = $connection->prepare($query);
            foreach($this->players as $index => $player) {
                $name = $player->string();
                $stm->bindParam(':username', $name);
                $success = $stm->execute();
                if (!$success) {
                    throw new InvalidArgumentException("We seem to have a problem with our database (Could not save squadID for Player ".$name."). 
                    Please try again later or contact an admin."); 
                }
                //the leader is not in the player array:
                if ($index == 0) {
                    $stm->bindParam(":username", $this->leader);
                    $success = $stm->execute();
                    if (!$success) {
                        throw new InvalidArgumentException("We seem to have a problem with our database (Could not save squadID for Player). 
                        Please try again later or contact an admin."); 
                    }
                }
            }
        }

        /*
        Update a squad: Set its side and set status to activated
        - parameter name: The name of the squad
        - side: Name of the side this squad plays for
        */
        public static function activate(PDO $connection, String $name, String $side) {
            //TODO: Use one query with join
            $query = "SELECT id FROM Side WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $side);
            $stm->execute();
            if ($stm->rowCount() != 1) {
                throw new InvalidArgumentException("<Squad::activate> Did not find a side named ".$side);
            }
            $sideID = $stm->fetch(PDO::FETCH_ASSOC)['id'];
            $query = "UPDATE Squad SET status = 'active', sideID = :id WHERE name = :name";
            //echo("<h1>".$query."</h1>");
            $stm = $connection->prepare($query);
            $stm->bindParam(":id", $sideID);
            $stm->bindParam(":name", $name);
            if (!$stm->execute()) {
                throw new InvalidArgumentException("We seems to have a problem with our database. Try again later.");
            }
        }

        /*
        Activate or deactivate a squad that already has a side
        */
        static public function changeStatus(PDO $connection, String $name, bool $activate) {
            $query = "UPDATE Squad SET status = :status WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $name);
            $action = $activate == true ? 'active' : 'inactive';
            $stm->bindParam(":status", $action);
            if (!$stm->execute()) {
                throw new InvalidArgumentException("We seems to have a problem with our database. Try again later.");
            }
        }

        //private function: get leader id
        private function getLeaderID(PDO $connection) {
            $query = "SELECT id FROM Player WHERE username = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $this->leader);
            $success = $stm->execute();
            if (!$success) {
                throw new InvalidArgumentException("We seem to have a problem with our database. 
                    Please try again later or contact an admin."); 
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        }

        //private function: get squad id => TODO: Make one function with parameter 
        private function getSquadID(PDO $connection) {
            $query = "SELECT id FROM Squad WHERE name = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name", $this->name->string());
            $success = $stm->execute();
            if (!$success) {
                throw new InvalidArgumentException("We seem to have a problem with our database. 
                    Please try again later or contact an admin."); 
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            return $result[id];
        }

        //check if a player already is in a squad
        private function isInForeignSquad(PDO $connection, $names) {
            $query = "SELECT squadID FROM Player WHERE name = :name";
            $stm = $connection->prepare($query);
            $result = array();
            foreach($names as $name) {
                $stm->bindParam(":name", $name);
                $stm->execute();
                $temp = $stm->fetch(PDO::FETCH_ASSOC);
                $result[] = $temp['squadID'];
            }
            return $result;
        }


    }

?>