<?php

    require_once __DIR__ . '/../autoload.php';

    try {
        $app = new App\Core\Application();
        $app->run(); // ЗАМЕНИТЕ boot() на run()
    } catch (Exception $e) {
        // Базовый обработчик ошибок на случай, если ErrorHandler не сработал
        http_response_code(500);
        echo "<h1>Application Error</h1>";
        if ($_ENV['APP_ENV'] === 'development') {
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            echo "<p>Please try again later.</p>";
        }
        exit;
    }