<?php

    namespace App\Core;

    class Permission
    {
        private static $permissions = [];
        private static $roleHierarchy = [];

        public static function initialize()
        {
            self::loadPermissions();
            self::loadRoleHierarchy();
        }

        public static function can($permission, $userRoles = null)
        {
            $userRoles = $userRoles ?: User::getRoles();

            // Приводим все к нижнему регистру для унификации
            $permission = strtolower(trim($permission));
            $userRoles = array_map('strtolower', array_map('trim', $userRoles));

            // Проверяем каждую роль пользователя
            foreach ($userRoles as $role) {
                if (self::roleHasPermission($role, $permission)) {
                    return true;
                }
            }

            return false;
        }

        private static function roleHasPermission($role, $permission)
        {
            // Проверяем прямые разрешения роли
            if (isset(self::$permissions[$role]) && in_array($permission, self::$permissions[$role])) {
                return true;
            }

            // Проверяем иерархию ролей
            if (isset(self::$roleHierarchy[$role])) {
                foreach (self::$roleHierarchy[$role] as $parentRole) {
                    if (self::roleHasPermission($parentRole, $permission)) {
                        return true;
                    }
                }
            }

            return false;
        }

        public static function getRolePermissions($role)
        {
            $role = strtolower(trim($role));
            return self::$permissions[$role] ?? [];
        }

        public static function validatePermission($permission)
        {
            // Собираем все существующие разрешения
            $allPermissions = [];
            foreach (self::$permissions as $rolePermissions) {
                $allPermissions = array_merge($allPermissions, $rolePermissions);
            }

            $allPermissions = array_unique($allPermissions);
            return in_array($permission, $allPermissions);
        }

        private static function loadPermissions()
        {
            // В реальном приложении можно загружать из базы данных или конфигурации
            self::$permissions = [
                'user' => [
                    'profile.view',
                    'profile.edit',
                    'profile.update'
                ],
                'admin' => [
                    'users.manage',
                    'users.create',
                    'users.update',
                    'users.delete',
                    'users.view',
                    'settings.manage',
                    'profile.view',
                    'profile.edit',
                    'profile.update'
                ]
            ];
        }

        private static function loadRoleHierarchy()
        {
            // Определяем иерархию ролей: администраторы имеют все права пользователей
            self::$roleHierarchy = [
                'admin' => ['user']
            ];
        }

        public static function addPermission($role, $permission)
        {
            $role = strtolower(trim($role));
            $permission = strtolower(trim($permission));

            if (!isset(self::$permissions[$role])) {
                self::$permissions[$role] = [];
            }

            if (!in_array($permission, self::$permissions[$role])) {
                self::$permissions[$role][] = $permission;
            }
        }

        public static function removePermission($role, $permission)
        {
            $role = strtolower(trim($role));
            $permission = strtolower(trim($permission));

            if (isset(self::$permissions[$role])) {
                self::$permissions[$role] = array_diff(self::$permissions[$role], [$permission]);
            }
        }
    }