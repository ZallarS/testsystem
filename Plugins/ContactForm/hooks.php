<?php

    use App\Core\Hooks;

    // Добавление пункта меню
    Hooks::addAction('admin_menu', function() {
        echo '<li><a href="/admin/contacts">Контакты</a></li>';
    });

    // Добавление JavaScript в футер
    Hooks::addAction('footer_scripts', function() {
        echo '<script>console.log("ContactForm plugin loaded");</script>';
    });