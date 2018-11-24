<?php
    require_once("validations/name.php");
    require_once("validations/email.php");
    require_once("validations/password.php");

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    class User {
        private $name       = "";
        private $firstName  = "";
        private $username   = "";
        private $email      = "";
        private $password   = "";
        private $error      = "";

        /*
        constructor takes in the $_POST array directly, which may be a bad idea
        */
        public function __construct($array) {
            if (!isset($array['name']) || !isset($array['firstName']) || !isset($array['username']) 
                || !isset($array['email']) || !isset($array['password'])) {
                throw InvalidArgumentException("Fill out all required form elements");
            }
            try {
                $this->name = new Name($array['name']);
            }    
            catch (InvalidArgumentException $e) {
                $this->error .= "Your name is invalid.";
            }
            try {
                $this->firstName = new Name($array['firstName']);
            }
            catch (InvalidArgumentException $e) {
                $this->error .= "Your first name is invalid.";
            }
            try {
                $this->username = new Name($array['username']);
            }
            catch(InvalidArgumentException $e) {
                $this->error .= "Your username is invalid";
            }
            try {
                $this->email = new Email($array['email']);
            }
            catch (InvalidArgumentException $e) {
                $this->error .= "Your email is invalid.";
            }
            try {
                $this->password = new Password($array['password'], $array['password2']);
            }
            catch (InvalidArgumentException $e) {
                $this->error .= "Your password is invalid.";
            }
        }

        /* 
        Construct a new user instance from the login data, ie username and password
        */
        public static function fromLogin(string $username, string $password, PDO $connection) {
            $query = "SELECT username, password FROM Player WHERE username = :username";
            $stm = $connection->prepare($query);
            $stm->bindParam(":username", $username);
            $stm->execute();
            //check if we got any results:
            if ($stm->rowCount() == 0) {
               return false;
            }
            else {
                //we found a user with the given name -> check password
                $result = $stm->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $result['password'])) {
                    $_SESSION['user'] = $username;
                    return true;
                }
                else {
                    return false;
                }
            }
        }

        /* 
        saves user data in the database. 
        returns true if the user was saved, false if the username already exists in the dataase
        TODO: Do we make the connection in the function or do we give the connection to the function ?
            -> For now, we give it to the function because I think this is a more flexible approach
        */
        public function save(PDO $connection) {
            $query = "SELECT username FROM Player WHERE username = :username OR email = :email";
            $stm = $connection->prepare($query);
            //TODO: Error check;
            $stm->bindParam(':username', $this->username);
            $stm->bindParam(':email', $this->email);
            $stm->execute();
            //If the username or email aready exists, give an error:
            if ($stm->rowCount() != 0) {
                $this->error = "Username or Email address is already in use. Are you sure you are not registered yet?";
            }
            //if not, write to database:
            else {
                $query = "INSERT INTO Player (name, firstName, email, username, password)
                        VALUES (:name, :firstName, :email, :username, :password)";
                $stm = $connection->prepare($query);
                //we bind on execution:
                $success = $stm->execute(array(
                    ':name' => $this->name,
                    ':firstName' => $this->firstName,
                    ':email' => $this->email,
                    ':username' => $this->username,
                    ':password' => $this->password
                ));
                if (!$success) {
                   $this->error = "We seem to have a problem with our database. Please try again later or contact an admin."; 
                }
            }
        }

        //getters:
        public function name() {
            return $this->name;
        }
        public function firstName() {
            return $this->firstName;
        }
        public function email() {
            return $this->email;
        }
        public function username() {
            return $this->username;
        }
        public function error() {
            return $this->error;
        }
    }

?>