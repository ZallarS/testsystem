<?php

    // Регистрация сервисов плагина
    $container->set('contact_service', function() {
        return new \Plugins\ContactForm\Services\ContactService();
    });