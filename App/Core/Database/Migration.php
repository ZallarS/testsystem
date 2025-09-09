<?php

    namespace App\Core\Database;

    use App\Core\Model;

    class Migration {
        protected $db;

        public function __construct() {
            $this->db = (new Model())->getDb();
        }

        public function createTable($tableName, $callback) {
            $blueprint = new Blueprint($tableName);
            $callback($blueprint);

            $sql = $blueprint->toSql();
            $this->db->exec($sql);
        }

        public function dropTable($tableName) {
            $this->db->exec("DROP TABLE IF EXISTS $tableName");
        }
    }