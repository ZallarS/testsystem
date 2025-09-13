<?php

    namespace App\Console\Commands;

    use App\Console\Command;
    use App\Core\Database\Migrator;

    class MigrateRunCommand extends Command
    {
        protected $name = 'migrate:run';
        protected $description = 'Run the database migrations';

        public function execute($input, $output)
        {
            $migrator = new Migrator();
            $migrator->run();

            $this->write("Migrations completed successfully.", $output);
            return 0;
        }
    }