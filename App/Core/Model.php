<?php

    namespace App\Core;

    use App\Core\Database\Connection;

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
            return $stmt->fetchAll();
        }

        public function find($id)
        {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        }

        public function create($data)
        {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute($data);
        }

        public function update($id, $data)
        {
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "$key = :$key";
            }
            $setClause = implode(', ', $setClause);

            $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            $data['id'] = $id;
            return $stmt->execute($data);
        }

        public function delete($id)
        {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
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
                $paramName = ':' . $key;
                $whereClause[] = "`$key` = $paramName";
                $bindings[$paramName] = $value;
            }

            $whereClause = implode(' AND ', $whereClause);
            $sql = "SELECT * FROM `{$this->table}` WHERE $whereClause";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll();
        }
    }