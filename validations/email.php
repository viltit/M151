<?php

    /* class email can construct an email adrees directly from a $_POST entry and will do all validation we need */
    class Email {
        
        private $email;

        public function __construct(string $email) {
            $error = "";
            if (!empty(trim($email)) && strlen(trim($email)) <= 40) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
                    $error = "Your email adress is invalid";
                }
                else {
                    $this->email = htmlspecialchars(trim($email));
                }
            }
            else {
                $error = "Your email adress is invalid";
            }
            if (!empty($error)) {
                throw new InvalidArgumentException($error);
            } 
        }

        public function __toString() {
            return $this->email;
        }
    }

    

?>