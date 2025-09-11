<?php

namespace Plugins\ContactForm;

use App\Core\AbstractPlugin;

class Plugin extends AbstractPlugin
{
    protected $name = 'ContactForm';
    protected $version = '1.0.0';
    protected $description = 'Плагин добавляет форму для записи, просмотр и обработку информации.';
    protected $author = 'TestSystem';

    public function boot()
    {
        // Код инициализации плагина
        error_log("ContactForm plugin booted");
    }

    public function activate()
    {
        // Код, выполняемый при активации плагина
        error_log("ContactForm plugin activated");

        // Запуск миграций при активации
        $this->runMigrations();
    }

    public function deactivate()
    {
        // Код, выполняемый при деактивации плагина
        error_log("ContactForm plugin deactivated");
    }

    private function runMigrations()
    {
        $migrationsPath = $this->getPath() . '/migrations';
        if (is_dir($migrationsPath)) {
            // Здесь можно добавить логику запуска миграций
            error_log("Running migrations for ContactForm plugin");
        }
    }

    public function registerRoutes($router)
    {
        $routesFile = $this->getPath() . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    public function registerServices($container)
    {
        $servicesFile = $this->getPath() . '/services.php';
        if (file_exists($servicesFile)) {
            require $servicesFile;
        }
    }
}