#!/usr/bin/env php
<?php

require_once __DIR__ . '/autoload.php';

use App\Core\Console\Kernel;

$kernel = new Kernel();
$kernel->handle($argv);