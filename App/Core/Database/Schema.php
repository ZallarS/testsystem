<?php

    namespace App\Core\Database;

    use Closure;
    use App\Core\Database\Connection;

    class Schema
    {
        protected static $db;

        public static function setConnection($db)
        {
            self::$db = $db;
        }

        public static function create($table, Closure $callback)
        {
            $blueprint = new Blueprint($table);
            $callback($blueprint);

            $sql = $blueprint->toSql();
            self::$db->exec($sql);
        }

        public static function table($table, Closure $callback)
        {
            $blueprint = new Blueprint($table, true); // true означает, что это ALTER TABLE
            $callback($blueprint);

            $sql = $blueprint->toSql();
            self::$db->exec($sql);
        }

        public static function dropIfExists($table)
        {
            self::$db->exec("DROP TABLE IF EXISTS `{$table}`");
        }

        public static function hasTable($table)
        {
            try {
                $result = self::$db->query("SHOW TABLES LIKE '{$table}'");
                return $result->rowCount() > 0;
            } catch (\Exception $e) {
                return false;
            }
        }

        public static function hasColumn($table, $column)
        {
            try {
                $result = self::$db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
                return $result->rowCount() > 0;
            } catch (\Exception $e) {
                return false;
            }
        }
    }