<?php

    return [
        'name' => 'Система тестирований',
        'env' => 'development', // Убедимся, что в режиме разработки
        'debug' => true,        // Включим отладку
        'url' => 'https://lib31.ru:83',
        'timezone' => 'UTC',
        'session' => [
            'lifetime' => 120,
            'expire_on_close' => false,
        ]
    ];