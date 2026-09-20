<?php
    namespace Core;

    // Creatws the modal parent class
    abstract class Model
    {
        protected \mysqli $db;

        public function __construct()
        {
            $this->db = Database::connect();
        }
    }
?>