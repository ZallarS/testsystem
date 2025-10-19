<?php

    namespace App\Services;

    use App\Models\User;
    use App\Models\Role;
    use App\Core\Database\Connection;

    class UserService
    {
        private $userModel;
        private $roleModel;

        public function __construct()
        {
            $this->userModel = new User();
            $this->roleModel = new Role();
        }

        public function getAllUsersWithRoles()
        {
            return $this->userModel->with('roles')->all();
        }

        public function createUser(array $userData)
        {
            $db = Connection::getInstance()->getPdo();

            try {
                $db->beginTransaction();

                // Создаем пользователя
                $user = [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => password_hash($userData['password'], PASSWORD_DEFAULT)
                ];

                if (!$this->userModel->create($user)) {
                    throw new \Exception("Failed to create user");
                }

                $userId = $db->lastInsertId();

                // Назначаем роли
                $this->assignRolesToUser($userId, $userData['roles']);

                $db->commit();
                return $userId;

            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }

        public function updateUser($userId, array $userData)
        {
            $db = Connection::getInstance()->getPdo();

            try {
                $db->beginTransaction();

                // Обновляем основные данные
                $updateData = [
                    'name' => $userData['name'],
                    'email' => $userData['email']
                ];

                if (isset($userData['password'])) {
                    $updateData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
                }

                if (!$this->userModel->update($userId, $updateData)) {
                    throw new \Exception("Failed to update user");
                }

                // Обновляем роли
                $this->userModel->removeAllRoles($userId);
                $this->assignRolesToUser($userId, $userData['roles']);

                $db->commit();

            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
        }

        private function assignRolesToUser($userId, array $roles)
        {
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

        public function validateUserExists($userId)
        {
            $user = $this->userModel->find($userId);
            return $user !== false;
        }
    }