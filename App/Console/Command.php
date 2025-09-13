<?php

    namespace App\Console;

    abstract class Command
    {
        protected $name;
        protected $description;

        abstract public function execute($input, $output);

        public function getName()
        {
            return $this->name;
        }

        public function getDescription()
        {
            return $this->description;
        }

        protected function write($message, $output)
        {
            if (is_object($output) && method_exists($output, 'writeln')) {
                $output->writeln($message);
            } else {
                echo $message . PHP_EOL;
            }
        }
    }