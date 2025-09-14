<?php

    use App\Core\Database\Seeder;

    class RoleSeeder extends Seeder
    {
        public function run()
        {
            $roles = [
                ['name' => 'admin', 'description' => 'Administrator with full access'],
                ['name' => 'user', 'description' => 'Regular user']
            ];

            foreach ($roles as $role) {
                $this->db->prepare("
                    INSERT INTO roles (name, description, created_at, updated_at) 
                    VALUES (:name, :description, NOW(), NOW())
                ")->execute($role);
            }

            echo "RoleSeeder executed successfully.\n";
        }
    }