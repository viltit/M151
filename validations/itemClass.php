<?php

/*
    Class ItemClass
    this is a very small class - still, we model it because it is linked to the database
    and we need a method to load it from there
*/

require_once($basePath."validations/name.php");

class ItemClass {
    private $name;

    public function __construct(String $name) {
        $this->name = new Name($name);
    }

    public function getName() {
        return $this->name;
    }

    /* Load all ItemClasses from the database and return them as array */
    public static function loadAll(PDO $connection) {
        
        $query = "SELECT name FROM ItemClass";
        $stm = $connection->prepare($query);    //no params to bind here, we could just execute
        $stm->execute();

        $classes = array();
        while ($result = $stm->fetch(PDO::FETCH_ASSOC)) {
            $classes[] = new ItemClass($result['name']);
        }

        return $classes;
    }

    public static function getIDFromName(PDO $connection, String $name) {

        $query = "SELECT id FROM ItemClass WHERE name = :name";
        $stm = $connection->prepare($query);
        $stm->bindParam(":name", $name);
        $stm->execute();
        if ($stm->rowCount() != 1) {
            throw new InvalidArgumentException("<ItemClass::getIDFromName> Could not find side id for name ".$name);
        }
        $result = $stm->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

}

?>