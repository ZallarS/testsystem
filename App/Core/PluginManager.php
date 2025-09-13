<?php

    namespace App\Core;

    class PluginManager {
        private $plugins = [];
        private $activePlugins = [];
        private $container;

        public function __construct($container)
        {
            $this->container = $container;

            try {
                $this->ensureStorageDirectoryExists();
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем работу
                error_log("PluginManager storage initialization failed: " . $e->getMessage());
            }

            $this->loadPlugins();
            $this->loadActivePlugins();
        }

        private function ensureStorageDirectoryExists()
        {
            try {
                $storagePath = $this->getStoragePath();

                // Если используем альтернативный путь, не создаём .htaccess
                if ($storagePath !== STORAGE_PATH) {
                    return;
                }

                // Создаём .htaccess только в основной директории
                $htaccess = STORAGE_PATH . '.htaccess';
                if (!file_exists($htaccess)) {
                    // Пытаемся создать, но не прерываем выполнение при ошибке
                    @file_put_contents($htaccess, "Deny from all\n");
                }
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем выполнение
                error_log("Storage directory initialization failed: " . $e->getMessage());
            }
        }

        private function getStoragePath()
        {
            // Если уже определён альтернативный путь, используем его
            if (defined('ALT_STORAGE_PATH') && is_writable(ALT_STORAGE_PATH)) {
                return ALT_STORAGE_PATH;
            }

            // Проверяем основную директорию
            if (is_dir(STORAGE_PATH) && is_writable(STORAGE_PATH)) {
                return STORAGE_PATH;
            }

            // Если основная директория недоступна для записи, используем временную
            $tempPath = sys_get_temp_dir() . '/testsystem_storage/';

            // Создаём временную директорию, если её нет
            if (!is_dir($tempPath)) {
                if (!mkdir($tempPath, 0755, true)) {
                    // Если не можем создать временную директорию, выбрасываем исключение
                    throw new \Exception("Cannot create storage directory: " . $tempPath);
                }
            }

            // Проверяем, что временная директория доступна для записи
            if (!is_writable($tempPath)) {
                throw new \Exception("Temporary storage directory is not writable: " . $tempPath);
            }

            // Определяем константу для альтернативного пути
            if (!defined('ALT_STORAGE_PATH')) {
                define('ALT_STORAGE_PATH', $tempPath);
            }

            return ALT_STORAGE_PATH;
        }

        public function loadPlugins()
        {
            $pluginsDir = PLUGINS_PATH;

            if (!is_dir($pluginsDir)) {
                throw new \Exception("Plugins directory not found: " . $pluginsDir);
            }

            $pluginFolders = scandir($pluginsDir);

            foreach ($pluginFolders as $folder) {
                // Пропускаем служебные папки и скрытые файлы
                if ($folder === '.' || $folder === '..' || $folder[0] === '.') {
                    continue;
                }

                // Проверяем, что это директория
                $pluginPath = $pluginsDir . $folder;
                if (!is_dir($pluginPath)) {
                    continue;
                }

                // Проверяем валидность имени папки
                if (!preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*$/', $folder)) {
                    error_log("Invalid plugin folder name: $folder");
                    continue;
                }

                $pluginFile = $pluginPath . '/Plugin.php';

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

        private function validatePluginSignature($pluginName, $manifestFile)
        {
            // Здесь может быть реализована проверка цифровой подписи
            // Например, проверка SHA256 хеша файлов плагина
            return true; // Временно всегда возвращаем true
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

        private function saveActivePlugins()
        {
            try {
                $active = array_keys($this->activePlugins);
                $storagePath = $this->getStoragePath();
                $file = $storagePath . 'active_plugins.json';

                $result = file_put_contents($file, json_encode($active, JSON_PRETTY_PRINT));

                if ($result === false) {
                    throw new \Exception("Failed to write to active plugins file");
                }

                return true;
            } catch (\Exception $e) {
                error_log("Error saving active plugins: " . $e->getMessage());
                return false;
            }
        }

        private function loadActivePlugins()
        {
            try {
                $storagePath = $this->getStoragePath();
                $file = $storagePath . 'active_plugins.json';

                if (!file_exists($file)) {
                    return;
                }

                if (!is_readable($file)) {
                    error_log("Active plugins file is not readable: " . $file);
                    return;
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
            } catch (\Exception $e) {
                error_log("Error loading active plugins: " . $e->getMessage());
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