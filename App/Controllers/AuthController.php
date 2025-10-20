<?php

    namespace App\Controllers;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Core\User;
    use App\Core\Validator;
    use App\Models\User as UserModel;

    use App\Jobs\SendWelcomeEmail;
    use App\Core\Queue\Queue;


    class AuthController extends Controller
    {
        private $userModel;

        public function __construct()
        {
            parent::__construct();
            $this->userModel = new UserModel();
        }

        public function login()
        {
            if (\App\Core\User::isLoggedIn()) {
                return \App\Core\Response::redirect('/');
            }

            return $this->view('auth/login', [
                'title' => 'Авторизация - My Application',
                'errors' => []
            ]);
        }

        public function register()
        {
            if (\App\Core\User::isLoggedIn()) {
                return \App\Core\Response::redirect('/');
            }
            $queue = new Queue();
            $queue->push(SendWelcomeEmail::class, ['user_id' => $userId]);
            return $this->view('auth/register', [
                'title' => 'Регистрация - My Application',
                'errors' => []
            ]);
        }

        public function processLogin()
        {
            error_log("Process login started. Session ID: " . (\App\Core\Session::id() ?? 'none'));

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            error_log("Login attempt for email: $email");

            // Валидация
            $errors = $this->validateLogin($email, $password);

            if (!empty($errors)) {
                error_log("Validation errors: " . implode(', ', $errors));
                $this->simulatePasswordVerification(); // Защита от timing-атак
                return $this->view('auth/login', [
                    'error' => 'Please fix the errors below',
                    'errors' => $errors,
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            // Используем UserService для поиска пользователя
            $userService = new \App\Services\UserService();
            $user = $userService->findUserByEmail($email);

            if (!$user) {
                $this->simulatePasswordVerification(); // Защита от timing-атак
                error_log("User not found for email: $email");
                return $this->view('auth/login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            // Проверяем пароль с использованием UserService
            $passwordValid = $userService->verifyPassword($password, $user['password']);

            if (!$passwordValid) {
                error_log("Invalid credentials for email: $email");
                \App\Core\AuditLogger::logLogin(null, $email, false);
                return $this->view('auth/login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            // Получаем пользователя с ролями через UserService
            $userWithRoles = $userService->getUserWithRoles($user['id']);

            if (!$userWithRoles) {
                error_log("Failed to load user roles for: $email");
                return $this->view('auth/login', [
                    'error' => 'Authentication error',
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            error_log("User roles: " . implode(', ', $userWithRoles['roles']));

            // Успешный вход
            User::login([
                'id' => $userWithRoles['id'],
                'email' => $userWithRoles['email'],
                'name' => $userWithRoles['name'],
                'roles' => $userWithRoles['roles']
            ]);
            \App\Core\AuditLogger::logLogin($userWithRoles['id'], $email, true);


            return $this->view('auth/login_success', [
                'title' => 'Login Successful - My Application'
            ]);
        }

        private function findUserSafely($email)
        {
            // Используем случайную задержку для дополнительной защиты
            $randomDelay = random_int(100000, 500000); // 100-500ms в микросекундах
            usleep($randomDelay);

            return $this->userModel->findByEmail($email);
        }

        private function verifyPasswordConstantTime($password, $hash)
        {
            // Используем hash_equals для постоянного времени сравнения
            $userService = new \App\Services\UserService();
            return $userService->verifyPassword($password, $hash);
        }

        private function simulatePasswordVerification()
        {
            // Симулируем проверку пароля для выравнивания времени ответа
            $dummyHash = '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ123456';
            password_verify('dummy_password', $dummyHash);

            // Случайная задержка
            $randomDelay = random_int(100000, 500000);
            usleep($randomDelay);
        }

        public function processRegister()
        {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Валидация
            $errors = $this->validateRegistration($name, $email, $password, $confirmPassword);

            if (!empty($errors)) {
                return $this->view('auth/register', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'title' => 'Register - My Application'
                ]);
            }

            // Создание пользователя
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ];

            try {
                if ($this->userModel->create($userData)) {
                    // Автоматический вход после регистрации
                    $user = $this->userModel->findByEmail($email);

                    if ($user) {
                        User::login([
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'name' => $user['name'],
                            'roles' => ['user'] // Роль по умолчанию для нового пользователя
                        ]);

                        return Response::redirect('/');
                    } else {
                        throw new \Exception('Failed to find created user');
                    }
                } else {
                    throw new \Exception('Failed to create user');
                }
            } catch (\Exception $e) {
                error_log("Registration error: " . $e->getMessage());

                $errors[] = 'Registration failed. Please try again.';
                return $this->view('auth/register', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email,
                    'title' => 'Register - My Application'
                ]);
            }
        }

        public function logout()
        {
            User::logout();
            \App\Core\AuditLogger::logLogout(\App\Core\User::getId());
            return Response::redirect('/');
        }

        private function validateLogin($email, $password)
        {
            $errors = [];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Пожалуйста, введите действительный адрес электронной почты';
            }

            if (empty($password)) {
                $errors[] = 'Пожалуйста, введите свой пароль';
            }

            return $errors;
        }

        private function validateRegistration($name, $email, $password, $confirmPassword)
        {
            $errors = [];

            // Более строгая проверка пароля
            if (strlen($password) < 8 ){
                $errors[] = 'Пароль должен содержать не менне 8 символов';
            }

            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
            }

            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
            }

            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Пароль должен содержать хотя бы одну цифру';
            }

            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = 'Пароль должен содержать хотя бы один специальный символ';
            }

            if (!Validator::string($name, 2, 50)) {
                $errors[] = 'Длина имени должна составлять от 2 до 50 символов';
            }

            if (!Validator::email($email)) {
                $errors[] = 'Требуется указать действительный адрес электронной почты';
            } else {
                // Проверяем, не существует ли уже пользователь с таким email
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser) {
                    $errors[] = 'Пользователь с таким адресом электронной почты уже существует';
                }
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Пароли не совпадают';
            }

            // Проверка на распространённые пароли
            $commonPasswords = ['password', '12345678'];
            if (in_array(strtolower($password), $commonPasswords)) {
                $errors[] = 'Пароль слишком простой';
            }

            return $errors;
        }
    }