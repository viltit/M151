<?php

    /*  class password takes a string for the password, a string for the confirmation password, and two optional
        parameters for minimum and maximum length  

        the password will be saved as a hash

        TODO: Add a pattern to the parameters    
    */
    class Password {
        private $pw = "";
        private $errors = "";

        function __construct(string $password, string $confirm, int $minlength = 3, int $maxlength = 20) {
            if(!empty(trim($password)) && strlen(trim($password)) <= $maxlength && strlen(trim($password)) >= $minlength) {
                if (trim($password) == trim($confirm)) {  
                    $this->pw = password_hash(trim($password), PASSWORD_DEFAULT);
                }
                else {
                    throw new InvalidArgumentException("Your password and confirmation password do  not match");
                }
            } 
            else {
                throw new InvalidArgumentException("Your password is not valid.");
            }
        }

        function __toString() {
            return $this->pw;
        }
    }
?>