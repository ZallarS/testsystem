<?php

    $router->get('/admin/example', function() use ($router) {
        error_log("Admin example route called");
        return 'Страница плагина Example';
    });

    $router->get('/example', function() use ($router) {
        error_log("Public example route called");
        return 'Публичная страница плагина Example';
    });