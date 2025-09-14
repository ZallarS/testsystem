<?php

    namespace App\Models;

    use App\Core\Model;
    use PDO;

    class Role extends Model {
        protected $table = 'roles';

        // Стандартные роли
        const ADMIN = 'admin';
        const USER = 'user';

        public function findByName($name)
        {
            // Приводим имя роли к нижнему регистру для унификации
            $name = strtolower(trim($name));

            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE LOWER(name) = :name");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function users() {
            $stmt = $this->db->prepare("
                SELECT users.* FROM users 
                INNER JOIN role_user ON users.id = role_user.user_id 
                WHERE role_user.role_id = :role_id
            ");
            $stmt->bindParam(':role_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }