<?php

    namespace App\Console\Commands;

    use App\Core\Database\Migrator;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    class MigrateCreateCommand extends Command
    {
        protected static $defaultName = 'migrate:create';

        protected function configure()
        {
            $this->setDescription('Create a new migration file')
                ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration')
                ->addOption('create', null, InputOption::VALUE_OPTIONAL, 'The table to be created')
                ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'The table to migrate');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $name = $input->getArgument('name');
            $options = [];

            if ($input->getOption('create')) {
                $options['create'] = $input->getOption('create');
            } elseif ($input->getOption('table')) {
                $options['table'] = $input->getOption('table');
            }

            $migrator = new Migrator();
            $migrator->create($name, $options);

            $output->writeln("Migration created successfully.");
            return Command::SUCCESS;
        }
    }