<?php

    namespace App\Core;

    interface PluginInterface {
        public function boot();
        public function activate();
        public function deactivate();
        public function registerRoutes($router);
        public function registerServices($container);
        public function registerEvents($dispatcher);
        public function registerHooks();

        // Добавляем геттеры для информации о плагине
        public function getName();
        public function getVersion();
        public function getDescription();
        public function getAuthor();
    }