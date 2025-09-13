<?php

    namespace App\Console\Commands;

    use App\Console\Command;
    use App\Core\Database\Migrator;

    class MigrateResetCommand extends Command
    {
        protected $name = 'migrate:reset';
        protected $description = 'Rollback all migrations';

        public function execute($input, $output)
        {
            $migrator = new Migrator();
            $migrator->reset();

            $this->write("Migrations reset successfully.", $output);
            return 0;
        }
    }