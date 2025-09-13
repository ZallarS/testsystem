<?php

    namespace App\Console\Commands;

    use App\Console\Command;
    use App\Core\Database\Migrator;

    class MigrateCreateCommand extends Command
    {
        protected $name = 'migrate:create';
        protected $description = 'Create a new migration file';

        public function execute($input, $output)
        {
            if (!isset($input[2])) {
                $this->write("Usage: php console migrate:create <MigrationName> [--create=table_name|--table=table_name]", $output);
                return 1;
            }

            $name = $input[2];
            $options = [];

            if (isset($input[3])) {
                parse_str(str_replace('--', '', $input[3]), $options);
            }

            $migrator = new Migrator();
            $migrator->create($name, $options);

            $this->write("Migration created successfully.", $output);
            return 0;
        }
    }