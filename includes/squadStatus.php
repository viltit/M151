<?php
    
    session_start();
    //this script should only be called from another scripts that already validated the session
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }

    function getSquadStatus(PDO $connection) {
        $result = array();
        try {
            $squad = Squad::load($connection, $_SESSION['user']);
            $result["squad"] = $squad;
            if ($squad->getStatus() == 'pending') {
                $result['message'] = "
                    Your squad is not validatet yet! You can not take any squad actions. Please contact an
                    admin. Together with you and other players, the admin will decide on which side you play
                    and then validate your squad.";
            }
            else if ($squad->getStatus() == 'inactive') {
                $result['message'] = "
                    Your squad is inactive. You can not take any squad actions. Please contact an admin
                    if you want to get active again.";
            }
        }
        catch (NoSquadException $e) {
            $result['error'] = "       
                You are not part of a squad yet! <br>
                <ul>
                <li>If you have at least three players ready, you can apply for your own squad
                <a href='squadRegister.php'><button type='button' class='btn btn-primary btn-sm'>here</button></a></li>
                <li>If you are part of an existing squad, your squad leader can register you.</li>
                <li>If you are alone and want to be part of a squad, please contanct an admin.</li>
                </ul>";
        }
        return $result;
    }



?>