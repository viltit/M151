<?php

    $pageTitle = "Testing";
    include("includes/head.php");

    function urlRequest($request) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $request
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    ini_set("display_errors", 1);
    include("dbConnect.php");

    //Test: Can we get data ?
    $query = "SELECT * FROM Player WHERE username=?";
    $stmt = $mysqli->prepare($query);
    $name = 'Momo';
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
?>

    <table class = "table">
        <tr><th>Vorname</th><th>Nachname</th><th>Email</th></tr>

<?php
    while ($row = $result->fetch_assoc()) {
        echo("<tr>");
        echo("<td>".$row['firstName']."</td><td>".$row['name']."</td><td>".$row['email']."</td>");
        echo("</tr>");
    }
    echo("</table>");
    $result->free();
    $stmt->close();

    /*  test: connect to mysql db run on a docker container  
    $servername = "172.17.0.2";
    $username = "root";
    $password = "1Vil@Tit2";

    try {
        $conn = new PDO("mysql:host=$servername;", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully";
    }
    catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    */


    /*
    $url = "https://community.bistudio.com/wiki/Arma_3_CfgWeapons_Weapons";
    $content = urlRequest($url);
    $startThrow = strpos($content, "Throw");
    $endThrow = strpos($content, "Put");
    $throw = substr($content, $startThrow, $endThrow - $startThrow);

    echo("<h1>".substr($throw, 'Objects&#160;'."</h1>"));

    //$throw = substr($throw, 0, substr($throw, "Objects&#160"));

    echo($throw);
    */

?>  

<?php
    /*
    $pageTitle = "Welcome";
    $content = "start";
    include "includes/head.php"; */
    include("includes/head.php");
?>

