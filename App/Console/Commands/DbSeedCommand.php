<?php

    namespace App\Console\Commands;

    use App\Console\Command;

    class DbSeedCommand extends Command
    {
        protected $name = 'db:seed';
        protected $description = 'Run database seeder';

        public function execute($input, $output)
        {
            $seederClass = $input[2] ?? 'UserSeeder';
            $seederFile = BASE_PATH . '/database/seeds/' . $seederClass . '.php';

            if (!file_exists($seederFile)) {
                $this->write("Seeder file not found: $seederFile", $output);
                return 1;
            }

            require_once $seederFile;

            if (!class_exists($seederClass)) {
                $this->write("Seeder class not found: $seederClass", $output);
                return 1;
            }

            $seeder = new $seederClass();
            $seeder->run();

            $this->write("Seeder executed successfully.", $output);
            return 0;
        }
    }