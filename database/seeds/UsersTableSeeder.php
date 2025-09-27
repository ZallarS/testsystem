<?php

use App\Core\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Создаем администратора
        $adminData = [
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => password_hash('SecurePassword123!', PASSWORD_DEFAULT)
            // created_at и updated_at добавятся автоматически через DEFAULT VALUES
        ];

        $existingAdmin = (new User())->findByEmail($adminData['email']);

        if (!$existingAdmin) {
            $userModel = new User();
            if ($userModel->create($adminData)) {
                $adminId = $userModel->getDb()->lastInsertId();
                $this->assignAdminRole($adminId);
                error_log("Admin user created with ID: $adminId");
            } else {
                error_log("Failed to create admin user");
            }
        } else {
            error_log("Admin user already exists");
            $this->assignAdminRole($existingAdmin['id']);
        }

        // Создаем тестового пользователя
        $userData = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => password_hash('UserPassword123!', PASSWORD_DEFAULT)
        ];

        $existingUser = (new User())->findByEmail($userData['email']);

        if (!$existingUser) {
            $userModel = new User();
            if ($userModel->create($userData)) {
                $userId = $userModel->getDb()->lastInsertId();
                $this->assignUserRole($userId);
                error_log("Test user created with ID: $userId");
            } else {
                error_log("Failed to create test user");
            }
        } else {
            error_log("Test user already exists");
            $this->assignUserRole($existingUser['id']);
        }
    }

    private function assignAdminRole($userId)
    {
        $this->assignRole($userId, 'admin');
    }

    private function assignUserRole($userId)
    {
        $this->assignRole($userId, 'user');
    }

    private function assignRole($userId, $roleName)
    {
        $roleModel = new Role();
        $role = $roleModel->findByName($roleName);

        if ($role) {
            $userModel = new User();
            $userModel->id = $userId;
            $userModel->assignRole($userId, $role['id']);
            error_log("Assigned role '$roleName' to user ID: $userId");
        } else {
            error_log("Role '$roleName' not found for user ID: $userId");
        }
    }
}