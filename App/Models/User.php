<?php

    namespace App\Models;

    use App\Core\Model;
    use PDO;

    class User extends Model
    {
        protected $table = 'users';

        // Константы ролей
        const ROLE_USER = 'user';
        const ROLE_ADMIN = 'admin';

        public function roles()
        {
            if (!isset($this->id) || !is_numeric($this->id)) {
                return [];
            }

            try {
                $stmt = $this->db->prepare("
                SELECT roles.name FROM roles 
                INNER JOIN role_user ON roles.id = role_user.role_id 
                WHERE role_user.user_id = :user_id");
                $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Exception $e) {
                error_log("Error fetching roles for user {$this->id}: " . $e->getMessage());
                return [];
            }
        }

        public function findByEmail($email)
        {
            if (!is_string($email) || empty($email)) {
                return false;
            }

            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function hasRole($roleName)
        {
            if (!isset($this->id) || !is_numeric($this->id)) {
                return false;
            }

            if (!in_array($roleName, [self::ROLE_USER, self::ROLE_ADMIN])) {
                return false;
            }

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

        public function all()
        {
            $stmt = $this->db->prepare("
            SELECT users.*, GROUP_CONCAT(roles.name) as roles 
            FROM users 
            LEFT JOIN role_user ON users.id = role_user.user_id 
            LEFT JOIN roles ON role_user.role_id = roles.id 
            GROUP BY users.id");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function assignRole($userId, $roleId)
        {
            try {
                // Сначала проверяем существование роли
                $roleCheck = $this->db->prepare("SELECT id FROM roles WHERE id = :role_id");
                $roleCheck->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $roleCheck->execute();

                if (!$roleCheck->fetch()) {
                    error_log("Role ID $roleId does not exist in roles table");
                    return false;
                }

                // Проверяем, нет ли уже такой роли у пользователя
                $checkStmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM role_user 
            WHERE user_id = :user_id AND role_id = :role_id");
                $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $checkStmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                $checkStmt->execute();
                $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] == 0) {
                    $insertStmt = $this->db->prepare("
                INSERT INTO role_user (user_id, role_id, created_at, updated_at) 
                VALUES (:user_id, :role_id, NOW(), NOW())");
                    $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $insertStmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
                    $insertResult = $insertStmt->execute();

                    error_log("Assigned role ID $roleId to user ID $userId. Success: " . ($insertResult ? 'yes' : 'no'));
                    error_log("Last insert ID: " . $this->db->lastInsertId());
                    return $insertResult;
                }

                error_log("Role ID $roleId already assigned to user ID $userId");
                return true;
            } catch (\PDOException $e) {
                error_log("PDO Error in assignRole: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                return false;
            }
        }

        public function removeAllRoles($userId)
        {
            try {
                $stmt = $this->db->prepare("DELETE FROM role_user WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $result = $stmt->execute();

                error_log("Removed all roles for user ID: $userId. Affected rows: " . $stmt->rowCount());
                return $result;
            } catch (\PDOException $e) {
                error_log("PDO Error in removeAllRoles: " . $e->getMessage());
                error_log("SQL State: " . $e->getCode());
                return false;
            }
        }


        public function create($data)
        {
            // Проверяем, есть ли столбец created_at в таблице
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE 'created_at'");
            $stmt->execute();
            $createdAtExists = $stmt->fetch();

            if ($createdAtExists) {
                $columns = implode(', ', array_keys($data));
                $placeholders = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO {$this->table} ($columns, created_at) VALUES ($placeholders, NOW())";
            } else {
                $columns = implode(', ', array_keys($data));
                $placeholders = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            }

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        }

        public function update($id, array $data)
        {

            if (!is_numeric($id)) {
                throw new \InvalidArgumentException('ID must be numeric');
            }

            // Если обновляется пароль, хешируем его
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Валидация email, если он обновляется
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format");
            }

            return parent::update($id, $data);
        }
    }