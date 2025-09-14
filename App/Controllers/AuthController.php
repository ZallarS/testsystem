<?php

    namespace App\Controllers;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Core\User;
    use App\Models\User as UserModel;

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
            if (User::isLoggedIn()) {
                return Response::redirect('/');
            }

            return $this->view('auth/login', [
                'title' => 'Авторизация',
                'errors' => []
            ]);
        }

        public function register()
        {
            if (User::isLoggedIn()) {
                return Response::redirect('/');
            }

            return $this->view('auth/register', [
                'title' => 'Регистрация',
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
                return $this->view('auth/login', [
                    'error' => 'Please fix the errors below',
                    'errors' => $errors,
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            // Поиск пользователя в базе данных
            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                error_log("Invalid credentials for email: $email");
                return $this->view('auth/login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            error_log("User found: " . $user['id']);

            // Загружаем роли пользователя
            $userModelInstance = new \App\Models\User();
            $userModelInstance->id = $user['id'];
            $roles = $userModelInstance->roles();

            error_log("User roles: " . implode(', ', $roles));

            // Успешный вход
            User::login([
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'roles' => $roles
            ]);

            // Проверяем, сохранились ли данные в сессии
            $loggedInUser = User::get();
            if ($loggedInUser && $loggedInUser['id'] == $user['id']) {
                error_log("SUCCESS: User data verified in session");
            } else {
                error_log("ERROR: User data not found in session after login");
                error_log("Session content: " . print_r($_SESSION, true));
            }

            // Для HTTP-режима используем промежуточную страницу
            if (php_sapi_name() !== 'cli') {
                error_log("Redirecting to login success page");
                return $this->view('auth/login_success', [
                    'title' => 'Login Successful - My Application'
                ]);
            }

            return Response::redirect('/');
        }

        public function processRegister()
        {
            $authService = new \App\Services\AuthService();
            $result = $authService->register($_POST['name'], $_POST['email'], $_POST['password']);

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

            if ($this->userModel->create($userData)) {
                // Автоматический вход после регистрации
                $user = $this->userModel->findByEmail($email);

                User::login([
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]);

                return Response::redirect('/');
            } else {
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
            return Response::redirect('/');
        }

        private function validateLogin($email, $password)
        {
            $errors = [];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address';
            }

            if (empty($password)) {
                $errors[] = 'Please enter your password';
            }

            return $errors;
        }

        private function validateRegistration($name, $email, $password, $confirmPassword)
        {
            $errors = [];

            // Более строгая проверка пароля
            if (strlen($password) < 10) {
                $errors[] = 'Password must be at least 10 characters';
            }

            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must contain at least one uppercase letter';
            }

            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must contain at least one lowercase letter';
            }

            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must contain at least one number';
            }

            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = 'Password must contain at least one special character';
            }

            if (!Validator::string($name, 2, 50)) {
                $errors[] = 'Name must be between 2 and 50 characters';
            }

            if (!Validator::email($email)) {
                $errors[] = 'Valid email is required';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            // Проверка на распространённые пароли
            $commonPasswords = ['password', '123456', 'qwerty', 'letmein'];
            if (in_array(strtolower($password), $commonPasswords)) {
                $errors[] = 'Password is too common';
            }

            return $errors;
        }
    }