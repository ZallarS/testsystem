<?php

    namespace App\Models;

    use App\Core\Model;
    use PDO;

    class User extends Model
    {
        protected $table = 'users';

        // Константы ролей
        const ROLE_USER = 'user';
        const ROLE_MODERATOR = 'moderator';
        const ROLE_ADMIN = 'admin';

        public function roles()
        {
            if (!isset($this->id) || !is_numeric($this->id)) {
                error_log("Invalid user ID in roles() method: " . ($this->id ?? 'undefined'));
                return [];
            }

            try {
                $stmt = $this->db->prepare("
            SELECT roles.name FROM roles 
            INNER JOIN role_user ON roles.id = role_user.role_id 
            WHERE role_user.user_id = :user_id
        ");
                $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
                $stmt->execute();

                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                error_log("Roles for user {$this->id}: " . print_r($roles, true));

                return $roles;
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

            if (!in_array($roleName, [self::ROLE_USER, self::ROLE_MODERATOR, self::ROLE_ADMIN])) {
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

        public function assignRole($roleId)
        {
            if (!isset($this->id) || !is_numeric($this->id)) {
                return false;
            }

            if (!is_numeric($roleId)) {
                return false;
            }

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

        public function create(array $data)
        {
            // Валидация обязательных полей
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new \InvalidArgumentException("Field {$field} is required");
                }
            }

            // Валидация email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format");
            }

            // Хеширование пароля
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Устанавливаем роль по умолчанию
            if (!isset($data['role'])) {
                $data['role'] = self::ROLE_USER;
            }

            return parent::create($data);
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