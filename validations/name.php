<?php

    /* class name takes a string direclty from $_POST and validates it.
        specify minLength and maxLength 
        used for firstName, lastName, username */
    class Name {
        private $name;

        public function __construct(string $name, int $minlength = 3, int $maxlength = 20) {
            if(!empty(trim($name)) && strlen(trim($name)) <= $maxlength && strlen(trim($name)) >= $minlength) {
                $this->name = htmlspecialchars(trim($name));
            } 
            else {
                throw new InvalidArgumentException("Your username is not valid.");
            }
        }

        public function string() {
            return $this->name;
        }
        public function __toString() {
            return $this->name;
        }
    }
?>