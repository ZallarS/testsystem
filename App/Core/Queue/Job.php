<?php

    namespace App\Core\Queue;

    abstract class Job
    {
        abstract public function handle($data);
    }