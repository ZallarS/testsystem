<?php

    namespace App\Models;

    use App\Core\Model;
    use PDO;

    class User extends Model {
        protected $table = 'users';

        public function roles() {
            $stmt = $this->db->prepare("
            SELECT roles.name FROM roles 
            INNER JOIN role_user ON roles.id = role_user.role_id 
            WHERE role_user.user_id = :user_id
        ");
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        public function hasRole($roleName) {
            $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM roles 
            INNER JOIN role_user ON roles.id = role_user.role_id 
            WHERE role_user.user_id = :user_id AND roles.name = :role_name
        ");
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':role_name', $roleName, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        }

        public function assignRole($roleId) {
            // Проверяем, нет ли уже такой роли у пользователя
            $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM role_user 
            WHERE user_id = :user_id AND role_id = :role_id
        ");
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] == 0) {
                $stmt = $this->db->prepare("
                INSERT INTO role_user (user_id, role_id, created_at, updated_at) 
                VALUES (:user_id, :role_id, NOW(), NOW())
            ");
                $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
                $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                return $stmt->execute();
            }

            return true;
        }

        public function removeRole($roleId) {
            $stmt = $this->db->prepare("
            DELETE FROM role_user 
            WHERE user_id = :user_id AND role_id = :role_id
        ");
            $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            return $stmt->execute();
        }

        public function findById($id) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function findByEmail($email) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function create($data) {
            // Устанавливаем роль по умолчанию
            if (!isset($data['role'])) {
                $data['role'] = self::ROLE_USER;
            }

            $sql = "INSERT INTO {$this->table} (name, email, password, role, created_at) 
                    VALUES (:name, :email, :password, :role, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':role', $data['role'], PDO::PARAM_STR);

            return $stmt->execute();
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

        public function updatePassword($id, $password) {
            $sql = "UPDATE {$this->table} SET password = :password, updated_at = NOW() WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        public function getUsersByRole($role) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = :role");
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function changeRole($id, $role) {
            $sql = "UPDATE {$this->table} SET role = :role, updated_at = NOW() WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        }
    }