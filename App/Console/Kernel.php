<?php

    namespace App\Core\Console;

    class Kernel
    {
        private $commands = [];

        public function __construct()
        {
            $this->registerCommands();
        }

        public function handle($args)
        {
            $commandName = $args[1] ?? null;

            if (!$commandName) {
                $this->showHelp();
                return;
            }

            if (!isset($this->commands[$commandName])) {
                $this->error("Command {$commandName} not found.");
                return;
            }

            $command = $this->commands[$commandName];
            $command->handle();
        }

        private function registerCommands()
        {
            $this->commands = [
                'queue:work' => new \App\Console\Commands\QueueWorkCommand(),
                'migrate' => new \App\Console\Commands\MigrateCommand(),
                'backup:create' => new \App\Console\Commands\BackupCreateCommand(),
            ];
        }

        private function showHelp()
        {
            $this->line("Available commands:");
            foreach ($this->commands as $name => $command) {
                $this->line("  {$name} - {$command->getDescription()}");
            }
        }

        private function error($message)
        {
            echo "\033[31m{$message}\033[0m" . PHP_EOL;
        }

        private function line($message)
        {
            echo $message . PHP_EOL;
        }
    }