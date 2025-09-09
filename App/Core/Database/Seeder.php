<?php

    namespace App\Core\Database;

    use App\Core\Database\Connection;

    abstract class Seeder
    {
        protected $db;

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
        }

        abstract public function run();

        public function call($seederClass)
        {
            require_once SEEDS_PATH . $seederClass . '.php';
            $seeder = new $seederClass();
            $seeder->run();
        }
    }