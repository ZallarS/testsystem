<?php

    namespace App\Core\Database;

    class Migration
    {
        protected $db;

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
            // Устанавливаем соединение для Schema
            Schema::setConnection($this->db);
        }

        public function createTable($tableName, $callback)
        {
            Schema::create($tableName, $callback);
        }

        public function dropTable($tableName)
        {
            Schema::dropIfExists($tableName);
        }

        public function table($tableName, $callback)
        {
            Schema::table($tableName, $callback);
        }
    }