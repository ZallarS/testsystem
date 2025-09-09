<?php

    namespace App\Core;

    interface PluginInterface {
        public function boot();
        public function activate();
        public function deactivate();
        public function registerRoutes($router);
        public function registerServices($container);

        // Сделаем эти методы необязательными
        public function registerEvents($dispatcher);
        public function registerHooks();
    }