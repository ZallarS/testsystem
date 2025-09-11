<?php

    // Маршруты плагина ContactForm
    $router->group('/contact', function() use ($router) {
        $router->get('/', function() {
            $controller = new \Plugins\ContactForm\Controllers\ContactController();
            return $controller->showForm();
        });

        $router->post('/submit', function() {
            $controller = new \Plugins\ContactForm\Controllers\ContactController();
            return $controller->submitForm();
        });

        $router->get('/success', function() {
            $controller = new \Plugins\ContactForm\Controllers\ContactController();
            return $controller->showSuccess();
        });
    });

    // Административные маршруты
    $router->group('/admin/contacts', function() use ($router) {
        $router->get('', function() {
            $controller = new \Plugins\ContactForm\Controllers\AdminController();
            return $controller->index();
        });

        $router->get('/view/{id}', function($id) {
            $controller = new \Plugins\ContactForm\Controllers\AdminController();
            return $controller->viewContact($id);
        });

        $router->get('/delete/{id}', function($id) {
            $controller = new \Plugins\ContactForm\Controllers\AdminController();
            return $controller->delete($id);
        });
    })->middleware([new \App\Middleware\AuthMiddleware(), new \App\Middleware\RoleMiddleware('admin')]);