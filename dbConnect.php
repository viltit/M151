<?php

    $host = '172.17.0.2';       //docker container
    $username = 'webuser';         //TODO: Do not connect with webApp as root
    $password = 'viti@webuser';    // password
    $database = 'a3'; // database

    // mit Datenbank verbinden
    $mysqli = new mysqli($host, $username, $password, $database);

    // fehlermeldung, falls verbindung fehl schlägt.
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
    }
    else {
        echo("<h1>Connected to db</h1>");
    }

?>