<?php

    namespace App\Core;

    class PluginManager {
        private $plugins = [];
        private $activePlugins = [];
        private $container;

        public function __construct($container) {
            $this->container = $container;
            $this->ensureStorageDirectoryExists();
            $this->loadPlugins();
            $this->loadActivePlugins();
        }

        private function ensureStorageDirectoryExists() {
            if (!is_dir(STORAGE_PATH)) {
                if (!mkdir(STORAGE_PATH, 0755, true)) {
                    // Попробуем создать с помощью sudo, если возможно
                    if (function_exists('shell_exec')) {
                        shell_exec('sudo mkdir -p ' . escapeshellarg(STORAGE_PATH));
                        shell_exec('sudo chmod 755 ' . escapeshellarg(STORAGE_PATH));
                        shell_exec('sudo chown www-data:www-data ' . escapeshellarg(STORAGE_PATH));
                    }

                    if (!is_dir(STORAGE_PATH)) {
                        throw new \Exception("Cannot create storage directory: " . STORAGE_PATH);
                    }
                }
            }

            // Проверяем права на запись
            if (!is_writable(STORAGE_PATH)) {
                // Попытаемся изменить права
                if (function_exists('shell_exec')) {
                    shell_exec('sudo chmod 755 ' . escapeshellarg(STORAGE_PATH));
                    shell_exec('sudo chown www-data:www-data ' . escapeshellarg(STORAGE_PATH));
                }

                if (!is_writable(STORAGE_PATH)) {
                    // Создадим временный файл в системной tmp директории
                    define('ALT_STORAGE_PATH', sys_get_temp_dir() . '/testsystem_storage/');
                    if (!is_dir(ALT_STORAGE_PATH)) {
                        mkdir(ALT_STORAGE_PATH, 0755, true);
                    }
                }
            }
        }

        private function getStoragePath() {
            if (defined('ALT_STORAGE_PATH') && is_writable(ALT_STORAGE_PATH)) {
                return ALT_STORAGE_PATH;
            }
            return STORAGE_PATH;
        }

        public function loadPlugins() {
            $pluginsDir = PLUGINS_PATH;

            if (!is_dir($pluginsDir)) {
                throw new \Exception("Plugins directory not found: " . $pluginsDir);
            }

            $pluginFolders = scandir($pluginsDir);

            foreach ($pluginFolders as $folder) {
                if ($folder === '.' || $folder === '..') continue;

                $pluginFile = $pluginsDir . $folder . '/Plugin.php';

                if (file_exists($pluginFile)) {
                    $pluginClass = "Plugins\\{$folder}\\Plugin";

                    if (!class_exists($pluginClass)) {
                        require_once $pluginFile;
                    }

                    if (class_exists($pluginClass)) {
                        try {
                            $plugin = new $pluginClass();
                            $this->plugins[$folder] = $plugin;
                        } catch (\Exception $e) {
                            error_log("Failed to initialize plugin {$folder}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        public function activatePlugin($pluginName) {
            if (!isset($this->plugins[$pluginName])) {
                throw new \Exception("Plugin {$pluginName} not found");
            }

            if ($this->isPluginActive($pluginName)) {
                throw new \Exception("Plugin {$pluginName} is already active");
            }

            try {
                $plugin = $this->plugins[$pluginName];
                $plugin->activate();

                $this->activePlugins[$pluginName] = $plugin;
                $this->saveActivePlugins();

                // Регистрируем сервисы, маршруты и хуки после активации
                $plugin->registerServices($this->container);
                $plugin->registerRoutes($this->container->get('router'));
                $plugin->registerHooks();

                return true;
            } catch (\Exception $e) {
                error_log("Failed to activate plugin {$pluginName}: " . $e->getMessage());
                throw $e;
            }
        }

        public function deactivatePlugin($pluginName) {
            if (!$this->isPluginActive($pluginName)) {
                throw new \Exception("Plugin {$pluginName} is not active");
            }

            try {
                $plugin = $this->activePlugins[$pluginName];
                $plugin->deactivate();

                unset($this->activePlugins[$pluginName]);
                $this->saveActivePlugins();

                return true;
            } catch (\Exception $e) {
                error_log("Failed to deactivate plugin {$pluginName}: " . $e->getMessage());
                throw $e;
            }
        }

        public function isPluginActive($pluginName) {
            return isset($this->activePlugins[$pluginName]);
        }

        public function getPlugin($pluginName) {
            return $this->plugins[$pluginName] ?? null;
        }

        public function getPlugins() {
            return $this->plugins;
        }

        public function getActivePlugins() {
            return $this->activePlugins;
        }

        private function saveActivePlugins() {
            try {
                $active = array_keys($this->activePlugins);
                $storagePath = $this->getStoragePath();
                $file = $storagePath . 'active_plugins.json';

                $result = file_put_contents($file, json_encode($active, JSON_PRETTY_PRINT));

                if ($result === false) {
                    // Попробуем использовать временную директорию
                    $tempFile = sys_get_temp_dir() . '/active_plugins.json';
                    $result = file_put_contents($tempFile, json_encode($active, JSON_PRETTY_PRINT));

                    if ($result === false) {
                        throw new \Exception("Failed to write to active plugins file");
                    }

                    // Попробуем переместить файл
                    if (!rename($tempFile, $file)) {
                        // Если не удалось переместить, будем использовать временный файл
                        define('ACTIVE_PLUGINS_FILE', $tempFile);
                    }
                }

                return true;
            } catch (\Exception $e) {
                error_log("Error saving active plugins: " . $e->getMessage());
                throw new \Exception("Failed to save active plugins: " . $e->getMessage());
            }
        }

        private function loadActivePlugins() {
            $storagePath = $this->getStoragePath();
            $file = $storagePath . 'active_plugins.json';

            // Если основной файл не существует, проверяем временный
            if (!file_exists($file) && defined('ACTIVE_PLUGINS_FILE')) {
                $file = ACTIVE_PLUGINS_FILE;
            }

            if (!file_exists($file)) {
                return;
            }

            if (!is_readable($file)) {
                // Попробуем изменить права
                if (function_exists('shell_exec')) {
                    shell_exec('sudo chmod 644 ' . escapeshellarg($file));
                }

                if (!is_readable($file)) {
                    error_log("Active plugins file is not readable: " . $file);
                    return;
                }
            }

            $content = file_get_contents($file);
            if ($content === false) {
                error_log("Failed to read active plugins file: " . $file);
                return;
            }

            $active = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Active plugins file contains invalid JSON: " . $file);
                return;
            }

            foreach ($active as $pluginName) {
                if (isset($this->plugins[$pluginName])) {
                    $this->activePlugins[$pluginName] = $this->plugins[$pluginName];
                }
            }
        }

        public function bootActivePlugins() {
            foreach ($this->activePlugins as $plugin) {
                try {
                    $plugin->boot();
                } catch (\Exception $e) {
                    error_log("Failed to boot plugin: " . $e->getMessage());
                }
            }
        }

        public function registerActivePluginRoutes($router) {
            foreach ($this->activePlugins as $plugin) {
                try {
                    $plugin->registerRoutes($router);
                } catch (\Exception $e) {
                    error_log("Failed to register routes for plugin: " . $e->getMessage());
                }
            }
        }

        public function registerActivePluginServices($container) {
            foreach ($this->activePlugins as $plugin) {
                try {
                    $plugin->registerServices($container);
                } catch (\Exception $e) {
                    error_log("Failed to register services for plugin: " . $e->getMessage());
                }
            }
        }

        public function registerActivePluginHooks() {
            foreach ($this->activePlugins as $plugin) {
                try {
                    $plugin->registerHooks();
                } catch (\Exception $e) {
                    error_log("Failed to register hooks for plugin: " . $e->getMessage());
                }
            }
        }
    }