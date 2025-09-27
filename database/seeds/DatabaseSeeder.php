<?php

class DatabaseSeeder
{
    public function run()
    {
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }

    private function call($seederClass)
    {
        $seederFile = SEEDS_PATH . $seederClass . '.php';

        if (!file_exists($seederFile)) {
            throw new Exception("Seeder file not found: $seederFile");
        }

        require_once $seederFile;

        if (!class_exists($seederClass)) {
            throw new Exception("Seeder class not found: $seederClass");
        }

        $seeder = new $seederClass();
        $seeder->run();
    }
}