<?php

    namespace App\Console\Commands;

    use App\Core\Database\Migrator;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class MigrateResetCommand extends Command
    {
        protected static $defaultName = 'migrate:reset';

        protected function configure()
        {
            $this->setDescription('Rollback all migrations');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $migrator = new Migrator();
            $migrator->reset();

            $output->writeln('Migrations reset successfully.');
            return Command::SUCCESS;
        }
    }