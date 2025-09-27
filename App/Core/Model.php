<?php

    namespace App\Core;

    use App\Core\Database\Connection;
    use PDO;

    class Model
    {
        protected $db;
        protected $table;

        public function __construct()
        {
            $this->db = Connection::getInstance()->getPdo();
        }

        public function all()
        {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function find($id)
        {
            if (!is_numeric($id)) {
                throw new \InvalidArgumentException('ID must be numeric');
            }

            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function create(array $data)
        {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

            $stmt = $this->db->prepare($sql);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            return $stmt->execute();
        }

        public function update($id, array $data)
        {
            if (!is_numeric($id)) {
                throw new \InvalidArgumentException('ID must be numeric');
            }

            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setClause);

            $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            return $stmt->execute();
        }

        public function getDb()
        {
            return $this->db;
        }

        public function delete($id)
        {
            if (!is_numeric($id)) {
                throw new \InvalidArgumentException('ID must be numeric');
            }

            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        public function where($conditions, $params = [])
        {
            $whereClause = [];
            $bindings = [];

            foreach ($conditions as $key => $value) {
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                    throw new \InvalidArgumentException('Invalid column name');
                }

                if (is_array($value)) {
                    $placeholders = implode(', ', array_fill(0, count($value), '?'));
                    $whereClause[] = "{$key} IN ({$placeholders})";
                    $bindings = array_merge($bindings, $value);
                } else {
                    $whereClause[] = "{$key} = ?";
                    $bindings[] = $value;
                }
            }

            $whereClause = implode(' AND ', $whereClause);
            $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function query($sql, $params = [])
        {
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
    }