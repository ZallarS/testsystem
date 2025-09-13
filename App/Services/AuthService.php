<?php

    namespace App\Services;

    use App\Models\User;

    class AuthService
    {
        public function register($name, $email, $password)
        {
            $userModel = new User();
            // логика регистрации
            return $userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);
        }
    }