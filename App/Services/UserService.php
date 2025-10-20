<?php

    namespace App\Services;

    use App\Models\User;
    use App\Models\Role;
    use App\Core\Database\Connection;
    use PDO;

    class UserService
    {
        private $userModel;
        private $roleModel;

        public function __construct()
        {
            $this->userModel = new User();
            $this->roleModel = new Role();
        }

        /**
         * Получить всех пользователей с ролями
         */
        public function getAllUsersWithRoles()
        {
            try {
                $users = $this->userModel->all();

                // Добавляем роли к каждому пользователю
                foreach ($users as &$user) {
                    $userModelInstance = new User();
                    $userModelInstance->id = $user['id'];
                    $user['roles'] = $userModelInstance->roles();
                }

                return $users;
            } catch (\Exception $e) {
                error_log("Error getting users with roles: " . $e->getMessage());
                throw new \Exception("Failed to retrieve users");
            }
        }

        /**
         * Создать нового пользователя
         */
        public function createUser(array $userData)
        {
            $db = Connection::getInstance()->getPdo();

            try {
                $db->beginTransaction();

                // Валидация обязательных полей
                if (empty($userData['name']) || empty($userData['email']) || empty($userData['password'])) {
                    throw new \InvalidArgumentException("Missing required user data");
                }

                // Проверка уникальности email
                $existingUser = $this->userModel->findByEmail($userData['email']);
                if ($existingUser) {
                    throw new \InvalidArgumentException("User with this email already exists");
                }

                // Создаем пользователя
                $user = [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $this->hashPassword($userData['password'])
                ];

                if (!$this->userModel->create($user)) {
                    throw new \Exception("Failed to create user in database");
                }

                $userId = $db->lastInsertId();

                // Назначаем роли (по умолчанию 'user', если не указано)
                $roles = $userData['roles'] ?? ['user'];
                $this->assignRolesToUser($userId, $roles);

                $db->commit();
                return $userId;

            } catch (\Exception $e) {
                $db->rollBack();
                error_log("User creation error: " . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Обновить пользователя
         */
        public function updateUser($userId, array $userData)
        {
            $db = Connection::getInstance()->getPdo();

            try {
                $db->beginTransaction();

                // Проверяем существование пользователя
                if (!$this->validateUserExists($userId)) {
                    throw new \InvalidArgumentException("User not found");
                }

                // Обновляем основные данные
                $updateData = [
                    'name' => $userData['name'],
                    'email' => $userData['email']
                ];

                // Проверяем уникальность email (исключая текущего пользователя)
                if (isset($userData['email'])) {
                    $existingUser = $this->userModel->findByEmail($userData['email']);
                    if ($existingUser && $existingUser['id'] != $userId) {
                        throw new \InvalidArgumentException("Email already exists");
                    }
                }

                if (isset($userData['password']) && !empty($userData['password'])) {
                    $updateData['password'] = $this->hashPassword($userData['password']);
                }

                if (!$this->userModel->update($userId, $updateData)) {
                    throw new \Exception("Failed to update user in database");
                }

                // Обновляем роли, если они предоставлены
                if (isset($userData['roles'])) {
                    $this->userModel->removeAllRoles($userId);
                    $this->assignRolesToUser($userId, $userData['roles']);
                }

                $db->commit();

            } catch (\Exception $e) {
                $db->rollBack();
                error_log("User update error: " . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Удалить пользователя
         */
        public function deleteUser($userId)
        {
            try {
                // Проверяем существование пользователя
                if (!$this->validateUserExists($userId)) {
                    throw new \InvalidArgumentException("User not found");
                }

                // Запрещаем удаление самого себя
                $currentUser = \App\Core\User::get();
                if ($currentUser && $currentUser['id'] == $userId) {
                    throw new \InvalidArgumentException("Cannot delete your own account");
                }

                return $this->userModel->delete($userId);

            } catch (\Exception $e) {
                error_log("User deletion error: " . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Назначить роли пользователю
         */
        private function assignRolesToUser($userId, array $roles)
        {
            // Валидация ролей
            $validRoles = ['user', 'admin'];
            foreach ($roles as $roleName) {
                if (!in_array($roleName, $validRoles)) {
                    throw new \InvalidArgumentException("Invalid role: {$roleName}");
                }
            }

            foreach ($roles as $roleName) {
                $role = $this->roleModel->findByName($roleName);

                if (!$role) {
                    throw new \Exception("Role not found: {$roleName}");
                }

                if (!$this->userModel->assignRole($userId, $role['id'])) {
                    throw new \Exception("Failed to assign role: {$roleName}");
                }
            }
        }

        /**
         * Хеширование пароля с использованием Argon2id
         */
        private function hashPassword($password)
        {
            // Проверяем длину пароля
            if (strlen($password) < 8) {
                throw new \InvalidArgumentException("Password must be at least 8 characters long");
            }

            $options = [
                'memory_cost' => 2048, // 2MB памяти
                'time_cost'   => 4,    // 4 итерации
                'threads'     => 3     // 3 потока
            ];

            $hash = password_hash($password, PASSWORD_ARGON2ID, $options);

            if ($hash === false) {
                throw new \RuntimeException("Password hashing failed");
            }

            return $hash;
        }

        /**
         * Проверка пароля с защитой от timing-атак
         */
        public function verifyPassword($password, $hash)
        {
            return password_verify($password, $hash);
        }

        /**
         * Проверка существования пользователя
         */
        public function validateUserExists($userId)
        {
            if (!is_numeric($userId) || $userId <= 0) {
                return false;
            }

            $user = $this->userModel->find($userId);
            return $user !== false;
        }

        /**
         * Найти пользователя по email
         */
        public function findUserByEmail($email)
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            return $this->userModel->findByEmail($email);
        }

        /**
         * Получить пользователя по ID с ролями
         */
        public function getUserWithRoles($userId)
        {
            $user = $this->userModel->find($userId);

            if (!$user) {
                return null;
            }

            $userModelInstance = new User();
            $userModelInstance->id = $user['id'];
            $user['roles'] = $userModelInstance->roles();

            return $user;
        }

        /**
         * Проверить, имеет ли пользователь роль
         */
        public function userHasRole($userId, $roleName)
        {
            $user = $this->getUserWithRoles($userId);
            return $user && in_array($roleName, $user['roles']);
        }

        /**
         * Обновить роли пользователя
         */
        public function updateUserRoles($userId, array $roles)
        {
            $db = Connection::getInstance()->getPdo();

            try {
                $db->beginTransaction();

                // Проверяем существование пользователя
                if (!$this->validateUserExists($userId)) {
                    throw new \InvalidArgumentException("User not found");
                }

                // Удаляем все текущие роли
                $this->userModel->removeAllRoles($userId);

                // Назначаем новые роли
                $this->assignRolesToUser($userId, $roles);

                $db->commit();

            } catch (\Exception $e) {
                $db->rollBack();
                error_log("User roles update error: " . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Поиск пользователей по имени или email
         */
        public function searchUsers($query, $limit = 10)
        {
            try {
                if (empty($query) || strlen($query) < 2) {
                    return [];
                }

                // Экранируем запрос для безопасности
                $safeQuery = preg_replace('/[^a-zA-Z0-9@._-]/', '', $query);

                $sql = "SELECT * FROM users WHERE name LIKE :query OR email LIKE :query LIMIT :limit";
                $stmt = $this->userModel->query($sql, [
                    'query' => '%' . $safeQuery . '%',
                    'limit' => $limit
                ]);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (\Exception $e) {
                error_log("User search error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Получить статистику пользователей
         */
        public function getUserStatistics()
        {
            try {
                $stats = [];

                // Общее количество пользователей
                $sql = "SELECT COUNT(*) as total FROM users";
                $stmt = $this->userModel->query($sql);
                $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Количество пользователей по ролям
                $sql = "
                    SELECT r.name as role, COUNT(ru.user_id) as count 
                    FROM roles r 
                    LEFT JOIN role_user ru ON r.id = ru.role_id 
                    GROUP BY r.name
                ";
                $stmt = $this->userModel->query($sql);
                $stats['users_by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Последние зарегистрированные пользователи
                $sql = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5";
                $stmt = $this->userModel->query($sql);
                $stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $stats;

            } catch (\Exception $e) {
                error_log("User statistics error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Валидация данных пользователя
         */
        public function validateUserData(array $userData, $isUpdate = false)
        {
            $errors = [];

            // Валидация имени
            if (empty($userData['name']) || strlen($userData['name']) < 2) {
                $errors[] = 'Name must be at least 2 characters long';
            }

            if (strlen($userData['name']) > 50) {
                $errors[] = 'Name cannot exceed 50 characters';
            }

            // Валидация email
            if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }

            // Валидация пароля (только для создания)
            if (!$isUpdate && (empty($userData['password']) || strlen($userData['password']) < 8)) {
                $errors[] = 'Password must be at least 8 characters long';
            }

            // Валидация ролей
            if (isset($userData['roles']) && is_array($userData['roles'])) {
                $validRoles = ['user', 'admin'];
                foreach ($userData['roles'] as $role) {
                    if (!in_array($role, $validRoles)) {
                        $errors[] = "Invalid role: {$role}";
                        break;
                    }
                }
            }

            return $errors;
        }
    }