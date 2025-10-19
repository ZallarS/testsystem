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
            \App\Core\Session::regenerate(); // Регенерируем сессию

            $sessionUserData = [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'name' => $userData['name'],
                'roles' => $userData['roles'],
                'login_time' => time(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];

            \App\Core\Session::set('user', $sessionUserData);

            // Дополнительная проверка
            if (!\App\Core\Session::get('user')) {
                throw new \RuntimeException('Failed to save user data in session');
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

        public static function set($key, $value)
        {
            self::start();

            if (self::$cliMode) {
                self::$cliSessionData[$key] = $value;
                return;
            }

            // Для HTTP-режима убедимся, что сессия активна
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION[$key] = $value;
                error_log("Session data set for key '$key': " . print_r($value, true));

                // Немедленно сохраняем изменения в сессии
                session_write_close();

                // И сразу же открываем сессию снова для последующих операций
                session_start();
            } else {
                error_log("Cannot set session value: session is not active");
            }
        }

        public static function hasRole($role) {
            $roles = self::getRoles();
            // Приводим все к нижнему регистру для унификации
            $role = strtolower($role);
            $roles = array_map('strtolower', $roles);

            error_log("Checking if user has role '$role'. Available roles: " . print_r($roles, true));
            return in_array($role, $roles);
        }

        public static function getRoles()
        {
            $user = self::get();
            if (!$user) {
                return ['user']; // Роль по умолчанию
            }

            // Уменьшаем время кэширования ролей с 5 минут до 1 минуты
            if (isset($user['roles']) && isset($user['roles_updated']) &&
                (time() - $user['roles_updated'] < 60)) { // 1 минута вместо 5
                return $user['roles'];
            }

            // Загружаем роли из базы данных
            $userId = $user['id'];
            $userModel = new \App\Models\User();
            $userData = $userModel->find($userId);

            if (!$userData) {
                return ['user'];
            }

            $userModel->id = $userId;
            $roles = $userModel->roles();

            // Обновляем роли в сессии
            $user['roles'] = $roles;
            $user['roles_updated'] = time();
            \App\Core\Session::set('user', $user);

            return $roles;
        }

        public static function isAdmin()
        {
            return self::hasRole('admin');
        }

        public static function can($permission) {
            // Здесь можно реализовать проверку конкретных разрешений
            // Пока просто проверяем роль
            $role = self::getRole();

            // Маппинг ролей к разрешениям
            $permissions = [
                'user' => ['view_profile', 'edit_profile'],
                'admin' => ['manage_all']
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