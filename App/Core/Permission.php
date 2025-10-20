<?php

namespace App\Core;

class Permission
{
    private static $permissions = [
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

    public static function can($permission, $userRoles = null)
    {
        $userRoles = $userRoles ?: User::getRoles();

        // Приводим все к нижнему регистру для унификации
        $permission = strtolower(trim($permission));
        $userRoles = array_map('strtolower', array_map('trim', $userRoles));

        // Администраторы имеют все разрешения
        if (in_array('admin', $userRoles)) {
            return true;
        }

        foreach ($userRoles as $role) {
            if (isset(self::$permissions[$role]) &&
                in_array($permission, self::$permissions[$role])) {
                return true;
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
}