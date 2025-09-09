<?php

    namespace App\Core;

    class User {
        private static $sessionStarted = false;

        public static function initSession() {
            if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
                session_start();
                self::$sessionStarted = true;
            }
        }

        public static function isLoggedIn() {
            self::initSession();
            return isset($_SESSION['user']);
        }

        public static function login($userData) {
            self::initSession();
            $_SESSION['user'] = $userData;
        }

        public static function logout() {
            self::initSession();
            unset($_SESSION['user']);
            session_destroy();
        }

        public static function get($key = null) {
            self::initSession();
            if ($key === null) {
                return $_SESSION['user'] ?? null;
            }

            return $_SESSION['user'][$key] ?? null;
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