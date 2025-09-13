<?php

    namespace App\Core;

    class User {
        private static $sessionStarted = false;

        public static function initSession()
        {
            if (!self::$sessionStarted) {
                try {
                    \App\Core\Session::start();
                    self::$sessionStarted = true;
                } catch (\Exception $e) {
                    error_log("Session initialization failed: " . $e->getMessage());
                    self::$sessionStarted = true;
                }
            }
        }

        public static function isLoggedIn() {
            self::initSession();
            return isset($_SESSION['user']);
        }

        public static function login($userData)
        {
            self::initSession();

            // Убедимся, что сессия активна перед записью
            if (\App\Core\Session::status() === PHP_SESSION_ACTIVE) {
                \App\Core\Session::set('user', $userData);

                // Добавим логирование для отладки
                error_log("User data saved to session: " . print_r($userData, true));
                error_log("Session data after login: " . print_r($_SESSION, true));
            } else {
                error_log("Cannot save user data: session is not active");
            }
        }


        public static function logout()
        {
            \App\Core\Session::destroy();
            self::$sessionStarted = false;
        }

        public static function get($key = null)
        {
            // Всегда инициализируем сессию перед чтением
            self::initSession();

            if ($key === null) {
                return \App\Core\Session::get('user');
            }

            $user = \App\Core\Session::get('user');
            return $user[$key] ?? null;
        }

        public static function getId() {
            return self::get('id');
        }

        public static function getName() {
            return self::get('name');
        }

        public static function getEmail() {
            return self::get('email');
        }

        public static function getRole()
        {
            // Получаем первую роль пользователя
            $roles = self::get('roles');
            return !empty($roles) ? $roles[0]['name'] : 'user';
        }

        public static function set($key, $value) {
            self::initSession();
            $_SESSION['user'][$key] = $value;
        }

        public static function hasRole($role) {
            $user = self::get();
            $userRoles = $user['roles'] ?? [];

            // Если roles - это массив объектов/массивов, извлекаем названия ролей
            $roleNames = [];
            foreach ($userRoles as $userRole) {
                if (is_array($userRole)) {
                $roleNames[] = $userRole['name'] ?? $userRole['role'] ?? null;
            } else if (is_object($userRole)) {
                    $roleNames[] = $userRole->name ?? $userRole->role ?? null;
                } else {
                    $roleNames[] = $userRole;
                }
            }

            return in_array($role, $roleNames);
        }

        public static function getRoles()
        {
            // Получаем первую роль пользователя
            $roles = self::get('roles');
            return !empty($roles) ? $roles[0]['name'] : 'user';
        }

        public static function isAdmin()
        {
            return self::hasRole('admin');
        }

        public static function isModerator()
        {
            return self::hasRole('moderator') || self::isAdmin();
        }

        public static function can($permission) {
            // Здесь можно реализовать проверку конкретных разрешений
            // Пока просто проверяем роль
            $role = self::getRole();

            // Маппинг ролей к разрешениям
            $permissions = [
                'user' => ['view_profile', 'edit_profile'],
                'moderator' => ['manage_content', 'manage_users'],
                'admin' => ['manage_plugins', 'manage_settings', 'manage_all']
            ];

            // Администраторы имеют все разрешения
            if ($role === 'admin') {
                return true;
            }

            // Проверяем разрешения для роли
            if (isset($permissions[$role]) && in_array($permission, $permissions[$role])) {
                return true;
            }

            return false;
        }
    }