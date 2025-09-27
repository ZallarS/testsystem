<?php

    namespace App\Core\Database;

    abstract class Seeder
    {
        protected $db;

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
        }

        abstract public function run();

        protected function call($seederClass)
        {
            $seederFile = SEEDS_PATH . $seederClass . '.php';

            if (!file_exists($seederFile)) {
                throw new \Exception("Seeder file not found: $seederFile");
            }

            require_once $seederFile;

            if (!class_exists($seederClass)) {
                throw new \Exception("Seeder class not found: $seederClass");
            }

            $seeder = new $seederClass();
            $seeder->run();
        }
    }