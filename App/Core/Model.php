<?php

    namespace App\Core;

    use App\Core\Database\Connection;
    use PDO;

    abstract class Model
    {
        protected $db;
        protected $table;
        protected $primaryKey = 'id';

        // Whitelist допустимых колонок
        protected $fillable = [];
        protected $guarded = ['id', 'created_at', 'updated_at'];

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
            $this->validateTableName();
        }

        private function validateTableName()
        {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->table)) {
                throw new \InvalidArgumentException('Invalid table name: ' . $this->table);
            }
        }

        public function find($id)
        {
            if (!is_numeric($id) && !is_string($id)) {
                throw new \InvalidArgumentException('ID must be numeric or string');
            }

            $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, is_numeric($id) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function where($conditions, $params = [], $operator = 'AND')
        {
            $whereClause = [];
            $bindings = [];

            foreach ($conditions as $key => $value) {
                if (!$this->isValidColumnName($key)) {
                    throw new \InvalidArgumentException("Invalid column name: {$key}");
                }

                if (is_array($value)) {
                    $placeholders = [];
                    foreach ($value as $index => $val) {
                        $paramName = ":" . str_replace('.', '_', $key) . "_{$index}";
                        $placeholders[] = $paramName;
                        $bindings[$paramName] = $val;
                    }
                    $whereClause[] = "`{$key}` IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $paramName = ":" . str_replace('.', '_', $key);
                    $whereClause[] = "`{$key}` = {$paramName}";
                    $bindings[$paramName] = $value;
                }
            }

            $whereClause = implode(" {$operator} ", $whereClause);
            $sql = "SELECT * FROM `{$this->table}` WHERE {$whereClause}";

            $stmt = $this->db->prepare($sql);
            foreach ($bindings as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        protected function isValidColumnName($column)
        {
            return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column);
        }

        public function create(array $data)
        {
            // Фильтруем данные по whitelist
            $filteredData = $this->filterData($data);

            $columns = implode(', ', array_map(function($col) {
                return "`{$col}`";
            }, array_keys($filteredData)));

            $placeholders = ':' . implode(', :', array_keys($filteredData));

            $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);

            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            return $stmt->execute();
        }

        protected function filterData(array $data)
        {
            $filtered = [];

            foreach ($data as $key => $value) {
                if (!$this->isValidColumnName($key)) {
                    continue;
                }

                // Проверяем whitelist/blacklist
                if (!empty($this->fillable) && !in_array($key, $this->fillable)) {
                    continue;
                }

                if (in_array($key, $this->guarded)) {
                    continue;
                }

                $filtered[$key] = $value;
            }

            return $filtered;
        }

        // Добавляем безопасные методы для сложных запросов
        public function query($sql, $params = [])
        {
            // Проверяем SQL на опасные операции
            if (!$this->isSafeQuery($sql)) {
                throw new \InvalidArgumentException("Potentially dangerous query detected");
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $stmt->bindValue($key + 1, $value);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            return $stmt;
        }

        private function isSafeQuery($sql)
        {
            $dangerousPatterns = [
                '/\bDROP\b/i',
                '/\bDELETE\s+FROM\b/i',
                '/\bTRUNCATE\b/i',
                '/\bINSERT\s+INTO\b/i',
                '/\bUPDATE\b/i',
                '/\bALTER\b/i',
                '/\bCREATE\b/i',
                '/\bEXEC\b/i',
                '/\bUNION\b/i'
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $sql)) {
                    return false;
                }
            }

            return true;
        }
    }