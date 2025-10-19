<?php

    namespace App\Controllers\Admin;

    use App\Core\BaseController;
    use App\Core\Response;
    use App\Services\UserService;
    use App\Validators\UserValidator;

    class UsersController extends Controller
    {
        private $userService;
        private $userValidator;

        public function __construct()
        {
            parent::__construct();
            $this->userService = new UserService();
            $this->userValidator = new UserValidator();
        }

        public function index()
        {
            try {
                $this->authorize('users.manage');

                $users = $this->userService->getAllUsersWithRoles();

                return $this->viewResponse('admin/users/index', [
                    'users' => $users,
                    'title' => 'Управление пользователями',
                    'activeMenu' => 'users'
                ]);
            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to load users');
            }
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
            try {
                $this->authorize('users.create');

                // Валидация
                $validationError = $this->validateRequest($this->userValidator->getCreationRules());
                if ($validationError) {
                    return $validationError;
                }

                $userData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'roles' => $_POST['roles'] ?? ['user']
                ];

                $user = $this->userService->createUser($userData);

                return $this->redirectResponse('/admin/users?message=Пользователь успешно создан');

            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to create user');
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
            try {
                $this->authorize('users.update');

                if (!$this->userValidator->validateUserId($id)) {
                    return $this->redirectResponse('/admin/users?error=Неверный ID пользователя');
                }

                $validationError = $this->validateRequest($this->userValidator->getUpdateRules());
                if ($validationError) {
                    return $validationError;
                }

                $userData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'roles' => $_POST['roles'] ?? []
                ];

                if (!empty($_POST['password'])) {
                    $userData['password'] = $_POST['password'];
                }

                $this->userService->updateUser($id, $userData);

                return $this->redirectResponse('/admin/users?message=Пользователь успешно обновлен');

            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to update user');
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