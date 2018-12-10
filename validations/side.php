<?php

class Side {

    private $name = "";
    private $credit = 0;
    private $leaderID = null;

    public function __construct($array) {
        if (!isset($array['name'])) {
            throw InvalidArgumentException("<Squad::Squad> Trying to initialize a Side-Object with no name");
        }
        try {
            $this->name = new Name($array['name']);
            if (isset($array['credits'])) {
                if (!is_numeric($array['credits'])) {
                    throw InvalidArgumentException("<Squad::Squad> Credits is not a numeric value!");
                }
                $this->credits = $array['credits'];
            }
            if (isset($array['leaderID'])) {
                //We ceck if the leaderID is value when its saved to the database
                $this->leaderID = $array['leaderID'];
            }
        }   

        catch (Exception $e) {
            throw $e;
        }
    }

    /* 
    Save this side in the database
    TODO: NOT TESTES YET
    */
    public function save(PDO $connection) {
        if ($this->name = "") {
            throw InvalidArgumentException("<Side::save> Trying to save a Side with no name.");
        }
        $query = "INSERT INTO Side (name, credits, leaderID)";
        $stm = $connection->prepare($query);
        $stm->bindParam(array(
            ":name" => $this->name,
            ":credits" => $this->credit,
            ":leaderID" => $this->leaderID
        ));
        $stm->execute();
    }

    /*
    Load all sides from the database and return them
     */
    public static function loadAll(PDO $connection) {
        $query = "SELECT * FROM Side";
        $stm = $connection->prepare($query);
        if (!$stm->execute()) {
            throw InvalidArgumentException("<Side::loadAll() Database query returned false");
        }
        $sides = array();
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $sides[] = new Side(array(
                "name" => $row['name'],
                "credits" => $row['credits'],
                "leaderID" => $row['leaderID']
            ));
        }        
        return $sides;
    }

    //getters
    public function getName()       { return $this->name; }
    public function getCredits()    { return $this->credit; }

}



?>