<?php

    use App\Core\Database\Seeder;
    use App\Models\User;
    use App\Models\Role;

    class UserSeeder extends Seeder
    {
        public function run()
        {
            // Очищаем таблицы
            $this->db->exec("DELETE FROM role_user");
            $this->db->exec("DELETE FROM users");
            $this->db->exec("DELETE FROM roles");

            echo "Cleared users, roles and role_user tables\n";

            // Создаем роли
            $roles = [
                ['name' => 'admin', 'description' => 'Administrator with full access'],
                ['name' => 'user', 'description' => 'Regular user']
            ];

            foreach ($roles as $role) {
                $this->db->prepare("
            INSERT INTO roles (name, description) 
            VALUES (:name, :description)
        ")->execute($role);
            }

            echo "Roles created successfully\n";

            // Получаем ID ролей
            $adminRole = $this->db->query("SELECT id FROM roles WHERE name = 'admin'")->fetch();
            $userRole = $this->db->query("SELECT id FROM roles WHERE name = 'user'")->fetch();

            // Создаем пользователей
            $users = [
                [
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role_id' => $adminRole['id'],
                ],
                [
                    'name' => 'User',
                    'email' => 'user@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role_id' => $userRole['id'],
                ]
            ];

            foreach ($users as $userData) {
                // Вставляем пользователя
                $this->db->prepare("
            INSERT INTO users (name, email, password) 
            VALUES (:name, :email, :password)
        ")->execute([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ]);

                // Получаем ID вставленного пользователя
                $userId = $this->db->lastInsertId();

                // Связываем пользователя с ролью
                $this->db->prepare("
            INSERT INTO role_user (user_id, role_id) 
            VALUES (:user_id, :role_id)
        ")->execute([
                    'user_id' => $userId,
                    'role_id' => $userData['role_id']
                ]);

                echo "Created user: " . $userData['email'] . " with role ID: " . $userData['role_id'] . "\n";
            }

            echo "UserSeeder executed successfully.\n";
        }
    }