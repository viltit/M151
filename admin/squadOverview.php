<?php
    ini_set("display_errors", 1);
    session_start();
    if (!isset($_SESSION['admin'])) {
        header("location:index.php");
    }


    //check if we have GET-parameters and if so, display the proper menu
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //print_r($_POST);
        if ($_POST['updateSquad'] == 'activate') {
            if (isset($_POST['name']) && isset($_POST['side'])) {
                try {
                    Squad::activate($connection, $_POST['name'], $_POST['side']);
                    $message .= "Squad ".$_POST['name']." is now active on side ".$_POST['side'];
                }
                catch (InvalidArgumentException $e) {
                    $error .= $e->getMessage();
                }
            }
        }
        else if ($_POST['updateSquad'] == 'deactivate') {
            //TODO: Should we ask the admin if he really want to take this action?
            if (isset($_POST['name'])) {
                try {
                    Squad::changeStatus($connection, $_POST['name'], false);
                    $message .= "Squad ".$_POST['name']." is now deactivated!";
                }
                catch (InvalidArgumentException $e) {
                    $error .= $e->getMessage();
                }
            }
        }
        else if ($_POST['updateSquad'] == 'reactivate') {
            //todo: almost the same code as above
            if (isset($_POST['name'])) {
                try {
                    Squad::changeStatus($connection, $_POST['name'], true);
                    $message .= "Squad ".$_POST['name']." is now reactivated!";
                }
                catch (InvalidArgumentException $e) {
                    $error .= $e->getMessage();
                }
            }
        }
    }

    //display errors or messages
    if (!empty($message)) {
        echo "<div class=\"alert alert-success\" role=\"alert\">".$message."</div>";
    }
    if (!empty($error)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">".$error."</div>";
    }

    //dispay all squads 
    try {
        $squads = Squad::loadAll($connection);
        displaySquads($squads, $connection);

    }
    catch (Exception $e) {
        $error .= $e->getMessage();
    }

    include($basePath."includes/foot.php");

?>

<?php
    function displaySquads($squads, $connection) {
        echo("<table class='table'>
        <thead>
        <tr>
            <th scope='col'>Name</th>
            <th scope='col'>URL</th>
            <th scope='col'>Image</th>
            <th scope='col'>Leader</th>
            <th scope='col'>Players</th>
            <th scope='col'>Side</th>
            <th scope='col'>Status</th>
        </tr>
        </thead>");

        $allSides = Side::loadAll($connection);

        foreach($squads as $squad) {
            echo("<tr>");
            echo("<td>".$squad->getName()."</td>");
            echo("<td>".$squad->getURL()."</td>");
            //todo: Image
            echo("<td></td>");
            echo("<td>".$squad->getLeader()."</td>");
            //TODO: For many players -> new Lines
            echo("<td>".$squad->getPlayersPrettyString()."</td>"); 
            //TODO: Print side name, not id
            //if squad is not activated, display a drop-down menu with a side to choose
            if ($squad->getStatus() == 'pending') {
                echo("<td><select class='form-control form-control-sm' name='side' form='activate".$squad->getName()."'>");
                foreach($allSides as $side) {
                    echo("<option value='".$side->getName()."'>".$side->getName()."</option>");
                }
                echo("</select></td>");
            }
            else {
                echo("<td>".$squad->getSide()."</td>");
            }
            //status: Mark red when the squad is pending. Add button for activation menu
            $style = "";
            $menu = "";
            if ($squad->getStatus() == 'pending') {
                $style = " class='table-danger'";
                $menu = "<form class='form-group' name='activate' method='POST' id='activate".$squad->getName()."'>
                        <input type='hidden' name='name' value='".$squad->getName()."'>
                        <input type='submit' class='btn btn-danger' name='updateSquad' value='activate'></form>";
            }
            else if ($squad->getStatus() == "active") {
                $style = " class='table-success'";
                $menu = "<form class='form-group' name='activate' method='POST' id='activate".$squad->getName()."'>
                        <input type='hidden' name='name' value='".$squad->getName()."'>
                        <input type='submit' class='btn btn-success' name='updateSquad' value='deactivate'></form>";
            }
            else if ($squad->getStatus() == 'inactive') {
                $style = " class='table-warning'";
                $menu = "<form class='form-group' name='activate' method='POST' id='activate".$squad->getName()."'>
                <input type='hidden' name='name' value='".$squad->getName()."'>
                <input type='submit' class='btn btn-warning' name='updateSquad' value='reactivate'></form>";
            }
            echo("<td".$style.">".$squad->getStatus().$menu);
            echo("<tr>");
        }
        echo("</table>");
    }
?>