<?php

    namespace App\Console;

    interface CommandInterface
    {
        public function execute(InputInterface $input, OutputInterface $output);
        public function getName();
        public function getDescription();
    }