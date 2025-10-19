<?php

    return [
        'secret' => $_ENV['APP_SECRET'] ?? 'your-secret-key-change-this-in-production',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',

        'session' => [
            'lifetime' => 120, // minutes
            'secure' => true,
            'same_site' => 'lax'
        ],

        'csrf' => [
            'lifetime' => 3600
        ]
    ];