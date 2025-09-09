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
                'title' => 'Login - My Application',
                'errors' => []
            ]);
        }

        public function register()
        {
            if (User::isLoggedIn()) {
                return Response::redirect('/');
            }

            return $this->view('auth/register', [
                'title' => 'Register - My Application',
                'errors' => []
            ]);
        }

        public function processLogin()
        {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Валидация
            $errors = $this->validateLogin($email, $password);

            if (!empty($errors)) {
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
                return $this->view('auth/login', [
                    'error' => 'Invalid email or password',
                    'email' => $email,
                    'title' => 'Login - My Application'
                ]);
            }

            // Загружаем роли пользователя
            $userModelInstance = new \App\Models\User();
            $userModelInstance->id = $user['id'];
            $roles = $userModelInstance->roles();

            // Успешный вход
            User::login([
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'roles' => $roles // Теперь это простой массив названий ролей
            ]);

            return Response::redirect('/');
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

            if (empty($name) || strlen($name) < 2) {
                $errors[] = 'Name must be at least 2 characters';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors[] = 'Email is already registered';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            return $errors;
        }
    }