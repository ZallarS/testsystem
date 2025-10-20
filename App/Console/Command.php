<?php

    namespace App\Core\Console;

    abstract class Command
    {
        protected $name;
        protected $description;

        abstract public function handle();

        public function getName()
        {
            return $this->name;
        }

        public function getDescription()
        {
            return $this->description;
        }

        protected function info($message)
        {
            echo "\033[32m{$message}\033[0m" . PHP_EOL;
        }

        protected function error($message)
        {
            echo "\033[31m{$message}\033[0m" . PHP_EOL;
        }

        protected function warn($message)
        {
            echo "\033[33m{$message}\033[0m" . PHP_EOL;
        }

        protected function line($message)
        {
            echo $message . PHP_EOL;
        }
    }