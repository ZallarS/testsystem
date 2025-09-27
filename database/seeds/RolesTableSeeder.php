<?php

use App\Core\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator with full access'
            ],
            [
                'name' => 'user',
                'description' => 'Regular user'
            ]
        ];

        foreach ($roles as $roleData) {
            // Проверяем, существует ли уже роль
            $existingRole = (new Role())->findByName($roleData['name']);

            if (!$existingRole) {
                $role = new Role();
                $role->create($roleData);
                error_log("Created role: " . $roleData['name']);
            } else {
                error_log("Role already exists: " . $roleData['name']);
            }
        }

        error_log("Roles seeded successfully");
    }
}