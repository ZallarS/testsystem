<?php

namespace App\Core;

class Permission
{
    private static $permissions = [
        'user' => [
            'profile.view',
            'profile.edit'
        ],
        'admin' => [
            'users.manage',
            'settings.manage',
            'profile.view',
            'profile.edit'
        ]
    ];

    public static function can($permission, $userRoles = null)
    {
        $userRoles = $userRoles ?: User::getRoles();

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
        return self::$permissions[$role] ?? [];
    }
}