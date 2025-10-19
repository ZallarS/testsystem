<?php

    require_once __DIR__ . '/../autoload.php';

    // Логируем входящие куки для диагностики
    if (php_sapi_name() !== 'cli') {
        error_log("Incoming cookies: " . print_r($_COOKIE, true));
    }

    // Инициализируем сессию только для HTTP-запросов
    if (php_sapi_name() !== 'cli') {
        try {
            \App\Core\Session::start();

            // Логируем данные сессии после инициализации - БЕЗОПАСНО
            $sessionData = \App\Core\Session::getAll();
            error_log("Session data after start: " . print_r($sessionData, true));

        } catch (\Exception $e) {
            error_log("Session initialization error: " . $e->getMessage());
        }
    }

    $app = new App\Core\Application();
    $app->boot();
    $app->run();