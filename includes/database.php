<?php
    class Database{

        /* variables needed for the database connection */
        private $host = "172.17.0.2";
        private $db_name = "a3";
        private $username = "webuser";
        private $password = "viti@webuser";
        public $connection;

        /* Constructor */
        function __construct() {
            $this->connection = null;
        }

        /* close database on destruction */
        function __destruct() {
            if (isset($this->connection)) {
                $this->connection = null;
            }
        }
      
        /* connect to database and return the pdo-object */
        public function connect() {
      
            $this->connection = null;
      
            try {
                $this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            }
            catch(PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
            }
            return $this->connection;
        }
    }


?>