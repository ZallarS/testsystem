<?php

    namespace App\Controllers\Admin;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Models\User as UserModel;

    class UsersController extends Controller
    {
        private $userModel;

        public function __construct()
        {
            parent::__construct();
            $this->userModel = new UserModel();
        }

        public function index()
        {
            $users = $this->userModel->all();

            return $this->view('admin/users/index', [
                'users' => $users,
                'title' => 'Управление пользователями',
                'activeMenu' => 'users'
            ]);
        }

        public function edit($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            // Получаем текущие роли пользователя
            $userModelInstance = new \App\Models\User();
            $userModelInstance->id = $user['id'];
            $userRoles = $userModelInstance->roles();

            return $this->view('admin/users/edit', [
                'user' => $user,
                'userRoles' => $userRoles, // Передаем массив ролей в шаблон
                'title' => 'Редактирование пользователя',
                'activeMenu' => 'users',
                'roles' => ['user', 'admin'] // Доступные роли
            ]);
        }

        public function delete($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            if ($this->userModel->delete($id)) {
                return Response::redirect('/admin/users?message=Пользователь успешно удален');
            } else {
                return Response::redirect('/admin/users?error=Ошибка при удалении пользователя');
            }
        }

        public function create()
        {
            return $this->view('admin/users/create', [
                'title' => 'Добавление пользователя',
                'activeMenu' => 'users',
                'roles' => ['user', 'admin'],
                'errors' => []
            ]);
        }

        public function store()
        {

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = $_POST['csrf_token'] ?? '';
                try {
                    \App\Core\CSRF::validateToken($token);
                } catch (\Exception $e) {
                    return Response::redirect('/admin/users?error=Недействительный CSRF-токен');
                }
            }

            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Валидация
            $errors = $this->validateUserData($name, $email, $password, $confirmPassword);

            if (!empty($errors)) {
                return $this->view('admin/users/create', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'title' => 'Добавление пользователя',
                    'activeMenu' => 'users',
                    'roles' => ['user', 'admin']
                ]);
            }

            // Создание пользователя
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $this->userModel->getDb()->beginTransaction();

                // Создание пользователя
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword
                ];

                if (!$this->userModel->create($userData)) {
                    throw new \Exception("Failed to create user");
                }

                $userId = $this->userModel->getDb()->lastInsertId();

                // Назначение ролей
                $roles = $_POST['roles'] ?? ['user']; // По умолчанию роль 'user'
                if (!empty($roles)) {
                    $this->updateUserRoles($userId, $roles);
                }

                $this->userModel->getDb()->commit();

                return Response::redirect('/admin/users?message=Пользователь успешно создан');
            } catch (\Exception $e) {
                $this->userModel->getDb()->rollBack();
                error_log("Error creating user: " . $e->getMessage());

                $errors[] = 'Ошибка при создании пользователя';
                return $this->view('admin/users/create', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'roles' => $roles,
                    'title' => 'Добавление пользователя',
                    'activeMenu' => 'users',
                    'availableRoles' => ['user', 'moderator', 'admin']
                ]);
            }
        }

        private function validateUserData($name, $email, $password, $confirmPassword)
        {
            $errors = [];

            if (empty($name)) {
                $errors[] = 'Имя пользователя обязательно';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Введите корректный email';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors[] = 'Пользователь с таким email уже существует';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Пароль должен содержать не менее 6 символов';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Пароли не совпадают';
            }

            return $errors;
        }

        public function update($id)
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = $_POST['csrf_token'] ?? '';
                try {
                    \App\Core\CSRF::validateToken($token);
                } catch (\Exception $e) {
                    return Response::redirect('/admin/users?error=Недействительный CSRF-токен');
                }
            }

            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            // Обновляем основные данные пользователя
            $data = [
                'name' => $_POST['name'] ?? $user['name'],
                'email' => $_POST['email'] ?? $user['email']
            ];

            // Если указан новый пароль, добавляем его
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            // Начинаем транзакцию
            $this->userModel->getDb()->beginTransaction();

            try {
                // Обновляем основные данные пользователя
                if ($this->userModel->update($id, $data)) {
                    // Обновляем роли пользователя
                    $newRoles = $_POST['roles'] ?? [];
                    if (!empty($newRoles)) {
                        $this->updateUserRoles($id, $newRoles);
                    }

                    // Фиксируем изменения
                    $this->userModel->getDb()->commit();

                    return Response::redirect('/admin/users?message=Пользователь успешно обновлен');
                } else {
                    $this->userModel->getDb()->rollBack();
                    return Response::redirect('/admin/users/edit/' . $id . '?error=Ошибка при обновлении пользователя');
                }
            } catch (\Exception $e) {
                $this->userModel->getDb()->rollBack();
                error_log("Error updating user: " . $e->getMessage());
                return Response::redirect('/admin/users/edit/' . $id . '?error=Ошибка при обновлении пользователя');
            }
        }

        private function updateUserRoles($userId, $roleNames)
        {
            error_log("Updating roles for user $userId to: " . implode(', ', $roleNames));

            try {
                $roleModel = new \App\Models\Role();

                // Удаляем все текущие роли пользователя
                if (!$this->userModel->removeAllRoles($userId)) {
                    error_log("Failed to remove roles for user: $userId");
                    return false;
                }

                // Добавляем новые роли
                foreach ($roleNames as $roleName) {
                    $role = $roleModel->findByName($roleName);

                    if (!$role) {
                        error_log("Role not found: $roleName");
                        continue; // Пропускаем несуществующие роли
                    }

                    if (!$this->userModel->assignRole($userId, $role['id'])) {
                        error_log("Failed to assign role {$role['id']} to user: $userId");
                        // Продолжаем добавлять другие роли, даже если одна не удалась
                    }
                }

                error_log("Successfully updated roles for user: $userId");
                return true;
            } catch (\Exception $e) {
                error_log("Error updating user roles: " . $e->getMessage());
                return false;
            }
        }

        private function updateUserRole($userId, $roleName)
        {
            error_log("Starting updateUserRole for user ID: $userId, role: $roleName");

            try {
                $roleModel = new \App\Models\Role();
                $role = $roleModel->findByName($roleName);

                if (!$role || !isset($role['id'])) {
                    error_log("Role '$roleName' not found or missing ID");
                    error_log("Available roles: " . print_r($roleModel->all(), true));
                    return false;
                }

                error_log("Found role: ID = {$role['id']}, Name = {$role['name']}");

                // Удаляем все текущие роли пользователя
                if (!$this->userModel->removeAllRoles($userId)) {
                    error_log("Failed to remove roles for user ID: $userId");
                    return false;
                }

                // Добавляем новую роль
                if (!$this->userModel->assignRole($userId, $role['id'])) {
                    error_log("Failed to assign role {$role['id']} to user ID: $userId");
                    return false;
                }

                error_log("Successfully updated role for user ID: $userId");
                return true;
            } catch (\Exception $e) {
                error_log("Exception in updateUserRole: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                return false;
            }
        }

        private function validateRoles($roles)
        {
            $validRoles = ['user', 'admin'];
            foreach ($roles as $role) {
                if (!in_array($role, $validRoles)) {
                    return false;
                }
            }
            return true;
        }

        public function roles()
        {
            if (!isset($this->id) || !is_numeric($this->id)) {
                return [];
            }

            try {
                $stmt = $this->db->prepare("
            SELECT roles.name FROM roles 
            INNER JOIN role_user ON roles.id = role_user.role_id 
            WHERE role_user.user_id = :user_id ");
                $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Exception $e) {
                error_log("Error fetching roles for user {$this->id}: " . $e->getMessage());
                return [];
            }
        }

    }