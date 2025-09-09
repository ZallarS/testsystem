<?php

    require_once __DIR__ . '/../autoload.php';

    $app = new App\Core\Application();
    $app->boot();
    $app->run();