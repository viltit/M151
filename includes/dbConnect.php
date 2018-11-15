<?php
    $host = '172.17.0.2';           //docker container on my host
    $user = 'webuser';         
    $password = 'viti@webuser';   
    $database = 'a3'; 

    // mit Datenbank verbinden
    $mysqli = new mysqli($host, $user, $password, $database);

    // fehlermeldung, falls verbindung fehl schlägt.
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
    }
?>