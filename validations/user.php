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
        private $id         = -1;  //used only for function load()

        /*
        constructor takes in the $_POST array directly, which may be a bad idea
        */
        public function __construct($array) {
            if (!isset($array['name']) || !isset($array['firstName']) || !isset($array['username']) 
                || !isset($array['email']) || !isset($array['password'])) {
                throw new InvalidArgumentException("Fill out all required form elements");
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
            //only check password when delivered
            if (isset($array['password']) && isset($array['password2'])) {
                try {
                    $this->password = new Password($array['password'], $array['password2']);
                }
                catch (InvalidArgumentException $e) {
                    $this->error .= "Your password is invalid.";
                }
            }
        }

        /* 
        Verify a user from the login data, ie username and password
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
        Create a user instance from username and password
        User names have to be unique, so this should work
        */
        public static function load(PDO $connection, String $username) {
            $query = "SELECT id, name, firstName, email, username, password FROM Player
                WHERE username = :name";
            $stm = $connection->prepare($query);
            $stm->bindParam(":name",$username);
            $stm->execute();
            if ($stm->rowCount() != 1) {
                throw new InvalidArgumentException("Did not find a user named ".$username);
            }
            $result = $stm->fetch(PDO::FETCH_ASSOC);
            $user = new User($result);
            $user->id = $result['id'];
            return $user;
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

        /* 
        update an existing user in the database
        This function CAN NOT update the users password. Use updatePassword() for this task.
        - parameter connection: the database connection
        - parameter array: holds all fields which should be updated
        - WARNING: User needs his id set for this function
        */
        public function update(PDO $connection, $array) {
            //update values given by $array
            foreach($array as $key => $value) {
                if ($key == 'name') {
                    $this->name = new Name($value);
                }
                else if ($key == 'firstName') {
                    $this->firstName = new Name($value);
                }
                else if ($key == 'username') {
                    $this->username = new Name($value);
                    echo("<h1>New username: ".$this->username()."</h1>");
                }
                else if ($key == 'email') {
                    $this->email = new Email($value);
                }
            }
            //TODO: Can we REALLY identify the user by password ?
            $query = "UPDATE Player SET name = :name,
                        firstName = :firstName,
                        username = :username,
                        email = :email
                        WHERE id = :id";
            $stm = $connection->prepare($query);
            $result = $stm->execute(array(
                ':firstName' => $this->firstName,
                ':name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'id' => $this->id
            ));
            if (!$result) {
                throw new InvalidArgumentException("Database failed to update user information.");
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

        //TODO: Remove, this is a bad idea from the beginning of the project
        public function error() {
            return $this->error;
        }
    }

?>