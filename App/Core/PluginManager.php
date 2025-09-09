<?php

    namespace App\Core;

    class PluginManager {
        private $plugins = [];
        private $activePlugins = [];

        public function __construct() {
            $this->loadPlugins();
        }

        public function loadPlugins()
        {
            $pluginsDir = PLUGINS_PATH;
            $pluginFolders = scandir($pluginsDir);

            error_log("Loading plugins from: " . $pluginsDir);
            error_log("Found folders: " . implode(', ', $pluginFolders));

            foreach ($pluginFolders as $folder) {
                if ($folder === '.' || $folder === '..') continue;

                $pluginFile = $pluginsDir . $folder . '/Plugin.php';
                error_log("Checking plugin file: " . $pluginFile);

                if (file_exists($pluginFile)) {
                    $pluginClass = "Plugins\\{$folder}\\Plugin";

                    if (!class_exists($pluginClass)) {
                        require_once $pluginFile;
                    }

                    if (class_exists($pluginClass)) {
                        $plugin = new $pluginClass();
                        $this->plugins[$folder] = $plugin;
                        error_log("Loaded plugin: " . $folder);
                    } else {
                        error_log("Class not found: " . $pluginClass);
                    }
                } else {
                    error_log("Plugin file not found: " . $pluginFile);
                }
            }

            error_log("Total plugins loaded: " . count($this->plugins));
        }

        public function enqueuePluginAssets() {
            foreach ($this->activePlugins as $pluginName => $plugin) {
                $assetsPath = "/assets/plugins/{$pluginName}/";
                $pluginAssetsDir = PLUGINS_PATH . $pluginName . '/assets/';

                if (is_dir($pluginAssetsDir)) {
                    // Добавляем CSS
                    if (is_dir($pluginAssetsDir . 'css')) {
                        echo "<!-- Styles for {$pluginName} -->\n";
                        $cssFiles = scandir($pluginAssetsDir . 'css');
                        foreach ($cssFiles as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                                echo "<link rel='stylesheet' href='{$assetsPath}css/{$file}'>\n";
                            }
                        }
                    }

                    // Добавляем JS
                    if (is_dir($pluginAssetsDir . 'js')) {
                        echo "<!-- Scripts for {$pluginName} -->\n";
                        $jsFiles = scandir($pluginAssetsDir . 'js');
                        foreach ($jsFiles as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                                echo "<script src='{$assetsPath}js/{$file}'></script>\n";
                            }
                        }
                    }
                }
            }
        }

        public function activatePlugin($pluginName)
        {
            error_log("=== SIMPLIFIED ACTIVATION ===");
            error_log("Activating plugin: " . $pluginName);

            // Просто добавляем плагин в активные без вызова методов
            if (isset($this->plugins[$pluginName])) {
                $this->activePlugins[$pluginName] = $this->plugins[$pluginName];
                error_log("Directly added to active plugins: " . $pluginName);

                // Сохраняем
                if ($this->saveActivePlugins()) {
                    error_log("Plugin directly activated and saved");
                    return true;
                } else {
                    error_log("Failed to save after direct activation");
                    unset($this->activePlugins[$pluginName]);
                    return false;
                }
            }

            error_log("Plugin not found: " . $pluginName);
            return false;
        }

        public function bootActivePlugins() {
            foreach ($this->activePlugins as $plugin) {
                $plugin->boot();
            }
        }

        public function registerPluginRoutes($router) {
            foreach ($this->activePlugins as $plugin) {
                $plugin->registerRoutes($router);
            }
        }

        public function registerPluginServices($container) {
            foreach ($this->activePlugins as $plugin) {
                $plugin->registerServices($container);
            }
        }

        private function saveActivePlugins()
        {
            error_log("=== SAVE ACTIVE PLUGINS CALLED ===");

            try {
                $active = array_keys($this->activePlugins);
                $file = STORAGE_PATH . 'active_plugins.json';

                error_log("Active plugins to save: " . implode(', ', $active));
                error_log("Saving to file: " . $file);

                // Создаем папку, если она не существует
                if (!is_dir(STORAGE_PATH)) {
                    error_log("Storage directory doesn't exist, creating: " . STORAGE_PATH);
                    if (!mkdir(STORAGE_PATH, 0755, true)) {
                        error_log('Cannot create storage directory');
                        return false;
                    }
                }

                // Проверяем права на запись
                if (!is_writable(STORAGE_PATH)) {
                    error_log('Storage directory is not writable');
                    return false;
                }

                // Записываем данные
                $result = file_put_contents($file, json_encode($active, JSON_PRETTY_PRINT));

                if ($result === false) {
                    error_log('Cannot write to file');
                    return false;
                }

                // Проверяем, что файл был записан правильно
                $writtenContent = file_get_contents($file);
                if ($writtenContent === false) {
                    error_log('Cannot read written file');
                    return false;
                }

                $writtenPlugins = json_decode($writtenContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Written file contains invalid JSON');
                    return false;
                }

                error_log("Active plugins successfully saved to file");
                return true;
            } catch (\Exception $e) {
                error_log('Error saving active plugins: ' . $e->getMessage());
                return false;
            }
        }

        public function loadActivePlugins()
        {
            $file = STORAGE_PATH . 'active_plugins.json';

            error_log("Loading active plugins from: $file");

            if (!file_exists($file)) {
                error_log('Active plugins file does not exist, creating empty file');
                $this->saveActivePlugins(); // Создаем файл с пустым массивом
                return;
            }

            if (!is_readable($file)) {
                error_log('Active plugins file is not readable');
                return;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                error_log('Failed to read active plugins file');
                return;
            }

            $active = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Active plugins file contains invalid JSON, recreating file');
                $this->saveActivePlugins();
                return;
            }

            error_log("Active plugins loaded from file: " . implode(', ', $active));

            foreach ($active as $pluginName) {
                if (isset($this->plugins[$pluginName]) && !isset($this->activePlugins[$pluginName])) {
                    $this->activePlugins[$pluginName] = $this->plugins[$pluginName];
                    error_log("Loaded active plugin: $pluginName");
                } else {
                    error_log("Plugin $pluginName not found or already active");
                }
            }

            error_log("Total active plugins loaded: " . count($this->activePlugins));
        }

        public function getActivePlugins() {
            return $this->activePlugins;
        }
        public function getPlugins()
        {
            error_log("Getting all plugins: " . implode(', ', array_keys($this->plugins)));
            return $this->plugins;
        }

        public function deactivatePlugin($pluginName)
        {
            error_log("=== DEACTIVATE PLUGIN METHOD CALLED ===");
            error_log("Deactivating plugin: " . $pluginName);

            if (isset($this->activePlugins[$pluginName])) {
                error_log("Plugin found in active plugins, proceeding with deactivation");

                try {
                    // Временно отключаем вызов deactivate() для тестирования
                    // $this->activePlugins[$pluginName]->deactivate();
                    error_log("Plugin deactivate method would be called here");

                    unset($this->activePlugins[$pluginName]);
                    error_log("Plugin removed from activePlugins array");

                    if ($this->saveActivePlugins()) {
                        error_log("Plugin deactivated and saved successfully");
                        return true;
                    } else {
                        error_log("Failed to save active plugins after deactivation");
                        // Откатываем изменения
                        $this->activePlugins[$pluginName] = $this->plugins[$pluginName];
                        return false;
                    }
                } catch (\Exception $e) {
                    error_log("Exception during deactivation: " . $e->getMessage());
                    return false;
                }
            }

            error_log("Plugin not found in active plugins");
            return false;
        }

        public function isPluginActive($pluginName)
        {
            $isActive = isset($this->activePlugins[$pluginName]);
            error_log("Checking if plugin $pluginName is active: " . ($isActive ? 'yes' : 'no'));
            return $isActive;
        }
    }