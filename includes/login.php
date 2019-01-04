<?php

    class InvalidLoginException extends Exception {       
        public function __construct($message = "", $code = 0, Exception $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

    class Login {
        //If anything goes wrong on login, this function throws -> no throw = login succeeded
        static function confirm(PDO $connection, String $username, String $password) {

            $cleanUsername = $cleanPassword = "";

            if(isset($username) && !empty(trim($username)) && strlen(trim($username)) <= 20) {
                $cleanUsername = htmlspecialchars(trim($username));
            }
            else {
                throw new InvalidLoginException();
            }

            if(isset($password) && !empty(trim($password)) && strlen(trim($password)) <= 20) {
                $cleanPassword = htmlspecialchars(trim($_POST['password']));
            }
            else {
                throw new InvalidLoginException();
            }

            $status = User::fromLogin($username, $password, $connection);
            if (!$status) {
                throw new InvalidLoginException();
            }
        }
    }
?>