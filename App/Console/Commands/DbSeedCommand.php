<?php

    namespace App\Console\Commands;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class DbSeedCommand extends Command
    {
        protected static $defaultName = 'db:seed';

        protected function configure()
        {
            $this->setDescription('Run database seeder')
                ->addArgument('seeder', InputArgument::OPTIONAL, 'The seeder class name', 'UserSeeder');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $seederClass = $input->getArgument('seeder');
            $seederFile = BASE_PATH . '/database/seeds/' . $seederClass . '.php';

            if (!file_exists($seederFile)) {
                $output->writeln("Seeder file not found: $seederFile");
                return Command::FAILURE;
            }

            require_once $seederFile;

            if (!class_exists($seederClass)) {
                $output->writeln("Seeder class not found: $seederClass");
                return Command::FAILURE;
            }

            $seeder = new $seederClass();
            $seeder->run();

            $output->writeln("Seeder executed successfully.");
            return Command::SUCCESS;
        }
    }