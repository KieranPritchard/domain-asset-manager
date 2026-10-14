<?php
    namespace Core;

    use PDO;

    // Creates the model parent class
    abstract class Model
    {
        protected PDO $db;

        public function __construct()
        {
            $this->db = Database::connect();
        }
    }
?>