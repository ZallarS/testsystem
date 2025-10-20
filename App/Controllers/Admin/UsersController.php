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

                $userService = new UserService();
                $users = $userService->getAllUsersWithRoles();

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
            try {
                $this->authorize('users.manage');

                // Проверяем, что пользователь имеет доступ к этому ресурсу
                if (!$this->canAccessUser($id)) {
                    return Response::redirect('/admin/users?error=Доступ запрещен');
                }

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
                    'userRoles' => $userRoles,
                    'title' => 'Редактирование пользователя',
                    'activeMenu' => 'users',
                    'roles' => ['user', 'admin']
                ]);

            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to load user edit page');
            }
        }

        public function delete($id)
        {
            try {
                $this->authorize('users.delete');

                // Проверяем, что пользователь имеет доступ к этому ресурсу
                if (!$this->canAccessUser($id)) {
                    return Response::redirect('/admin/users?error=Доступ запрещен');
                }

                $user = $this->userModel->find($id);

                if (!$user) {
                    return Response::redirect('/admin/users?error=Пользователь не найден');
                }

                // Запрещаем удаление самого себя
                if ($user['id'] == \App\Core\User::getId()) {
                    return Response::redirect('/admin/users?error=Нельзя удалить собственный аккаунт');
                }

                if ($this->userModel->delete($id)) {
                    return Response::redirect('/admin/users?message=Пользователь успешно удален');
                } else {
                    return Response::redirect('/admin/users?error=Ошибка при удалении пользователя');
                }

                \App\Core\AuditLogger::log('user_deletion', [
                    'deleted_user_id' => $userId
                ], \App\Core\User::getId());

            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to delete user');
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

                $userService = new UserService();

                // Валидация данных
                $validationErrors = $userService->validateUserData($_POST, false);
                if (!empty($validationErrors)) {
                    return $this->viewResponse('admin/users/create', [
                        'errors' => $validationErrors,
                        'title' => 'Добавление пользователя',
                        'activeMenu' => 'users',
                        'roles' => ['user', 'admin']
                    ]);
                }

                $userData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'roles' => $_POST['roles'] ?? ['user']
                ];

                $userId = $userService->createUser($userData);
                \App\Core\AuditLogger::logUserCreation(
                    \App\Core\User::getId(),
                    $userId,
                    ['name' => $userData['name'], 'email' => $userData['email']]
                );
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

                $userService = new UserService();

                // Валидация данных
                $validationErrors = $userService->validateUserData($_POST, true);
                if (!empty($validationErrors)) {
                    return $this->redirectResponse("/admin/users/edit/{$id}?error=" . urlencode(implode(', ', $validationErrors)));
                }

                $userData = [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'roles' => $_POST['roles'] ?? []
                ];

                if (!empty($_POST['password'])) {
                    $userData['password'] = $_POST['password'];
                }

                $userService->updateUser($id, $userData);

                \App\Core\AuditLogger::logUserUpdate(
                    \App\Core\User::getId(),
                    $id,
                    $userData
                );

                return $this->redirectResponse('/admin/users?message=Пользователь успешно обновлен');

            } catch (\Exception $e) {
                return $this->handleException($e, 'Failed to update user');
            }
        }

        private function canAccessUser($userId)
        {
            $currentUser = \App\Core\User::get();

            // Администраторы имеют доступ ко всем пользователям
            if (\App\Core\User::isAdmin()) {
                return true;
            }

            // Обычные пользователи могут редактировать только свой профиль
            return $currentUser['id'] == $userId;
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