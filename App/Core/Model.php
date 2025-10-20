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

            // Validate ID format
            if (is_string($id) && !preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
                throw new \InvalidArgumentException('Invalid ID format');
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
            // Enhanced data filtering
            $filteredData = $this->filterData($data);

            // Validate required fields
            if (empty($filteredData)) {
                throw new \InvalidArgumentException('No valid data provided for creation');
            }

            $columns = implode(', ', array_map(function($col) {
                return "`{$col}`";
            }, array_keys($filteredData)));

            $placeholders = ':' . implode(', :', array_keys($filteredData));

            $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);

            foreach ($filteredData as $key => $value) {
                // Type-based binding
                $type = PDO::PARAM_STR;
                if (is_int($value)) $type = PDO::PARAM_INT;
                if (is_bool($value)) $type = PDO::PARAM_BOOL;
                if (is_null($value)) $type = PDO::PARAM_NULL;

                $stmt->bindValue(":{$key}", $value, $type);
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

                // Check whitelist/blacklist
                if (!empty($this->fillable) && !in_array($key, $this->fillable)) {
                    continue;
                }

                if (in_array($key, $this->guarded)) {
                    continue;
                }

                // Sanitize values based on type
                if (is_string($value)) {
                    $value = \App\Core\Validator::sanitizeInput($value, 'sql');
                }

                $filtered[$key] = $value;
            }

            return $filtered;
        }

        // Добавляем безопасные методы для сложных запросов
        public function query($sql, $params = [])
        {
            try {
                // Validate SQL structure for dangerous operations
                if (!$this->isSafeQuery($sql)) {
                    throw new \Exception('Potentially dangerous query detected');
                }

                $stmt = $this->db->prepare($sql);

                foreach ($params as $key => $value) {
                    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    if (is_int($key)) {
                        $stmt->bindValue($key + 1, $value, $type);
                    } else {
                        $stmt->bindValue($key, $value, $type);
                    }
                }

                $stmt->execute();
                return $stmt;
            } catch (\PDOException $e) {
                error_log("Database query error: " . $e->getMessage());
                throw new \Exception("Database error occurred");
            }
        }

        public function rawQuery($sql, $allowedPatterns = [])
        {
            // Проверяем запрос на наличие опасных операций
            $dangerousPatterns = [
                '/\bDROP\b/i',
                '/\bDELETE\b/i',
                '/\bUPDATE\b/i',
                '/\bINSERT\b/i',
                '/\bALTER\b/i',
                '/\bCREATE\b/i'
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $sql) && !$this->isQueryAllowed($sql, $allowedPatterns)) {
                    throw new \InvalidArgumentException("Potentially dangerous query detected");
                }
            }

            return $this->query($sql);
        }

        private function isQueryAllowed($sql, $allowedPatterns)
        {
            foreach ($allowedPatterns as $pattern) {
                if (preg_match($pattern, $sql)) {
                    return true;
                }
            }
            return false;
        }

        private function isSafeQuery($sql)
        {
            $dangerousPatterns = [
                '/\bDROP\b/i',
                '/\bDELETE\s+FROM\b/i',
                '/\bUPDATE\s+\w+\s+SET\b/i',
                '/\bINSERT\s+INTO\b/i',
                '/\bALTER\s+TABLE\b/i',
                '/\bCREATE\s+TABLE\b/i',
                '/\bTRUNCATE\b/i'
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $sql)) {
                    return false;
                }
            }

            return true;
        }

        public function whereSafe($conditions, $params = [], $operator = 'AND')
        {
            $whereParts = [];
            $bindings = [];

            foreach ($conditions as $field => $value) {
                if (!$this->isValidColumnName($field)) {
                    throw new \InvalidArgumentException("Invalid column name: $field");
                }

                $paramName = ':' . str_replace('.', '_', $field);
                $whereParts[] = "`$field` = $paramName";
                $bindings[$paramName] = $value;
            }

            $whereClause = implode(" $operator ", $whereParts);
            $sql = "SELECT * FROM `{$this->table}` WHERE $whereClause";

            return $this->query($sql, $bindings);
        }
    }