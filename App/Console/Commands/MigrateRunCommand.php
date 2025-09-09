<?php

    namespace App\Console\Commands;

    use App\Core\Database\Migrator;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class MigrateRunCommand extends Command
    {
        protected static $defaultName = 'migrate:run';

        protected function configure()
        {
            $this->setDescription('Run the database migrations');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $migrator = new Migrator();
            $migrator->run();

            $output->writeln('Migrations completed successfully.');
            return Command::SUCCESS;
        }
    }