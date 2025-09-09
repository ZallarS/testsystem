<?php

    namespace App\Controllers\Admin;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Core\Application; // Правильный импорт


    class PluginsController extends Controller
    {
        private $pluginManager;

        public function __construct()
        {
            parent::__construct();

            // Получаем PluginManager через Application
            $this->pluginManager = Application::getContainer()->get('plugin_manager');

            if (!$this->pluginManager) {
                throw new \Exception('PluginManager not found in container');
            }
        }

        public function index()
        {
            try {
                $plugins = $this->pluginManager->getPlugins();
                $activePlugins = $this->pluginManager->getActivePlugins();

                return $this->view('admin/plugins/index', [
                    'plugins' => $plugins,
                    'activePlugins' => $activePlugins,
                    'title' => 'Plugin Management'
                ]);
            } catch (\Exception $e) {
                // Временно показываем ошибку для отладки
                return "Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            }
        }

        public function activate($pluginName)
        {
            error_log("=== ACTIVATE CONTROLLER CALLED ===");
            error_log("Activating plugin: " . $pluginName);

            // Проверяем, доступен ли плагин
            $allPlugins = $this->pluginManager->getPlugins();
            error_log("Available plugins: " . implode(', ', array_keys($allPlugins)));

            if ($this->pluginManager->isPluginActive($pluginName)) {
                error_log("Plugin $pluginName is already active");
                return Response::redirect('/admin/plugins?message=Plugin is already active');
            }

            if ($this->pluginManager->activatePlugin($pluginName)) {
                error_log("Plugin $pluginName activated successfully in controller");
                return Response::redirect('/admin/plugins?message=Plugin activated successfully');
            } else {
                error_log("Failed to activate plugin $pluginName in controller");
                return Response::redirect('/admin/plugins?message=Error activating plugin');
            }
        }

        public function deactivate($pluginName)
        {
            error_log("=== DEACTIVATE CONTROLLER CALLED ===");
            error_log("Deactivating plugin: " . $pluginName);

            if (!$this->pluginManager->isPluginActive($pluginName)) {
                error_log("Plugin $pluginName is not active");
                return Response::redirect('/admin/plugins?message=Plugin is not active');
            }

            if ($this->pluginManager->deactivatePlugin($pluginName)) {
                error_log("Plugin $pluginName deactivated successfully in controller");
                return Response::redirect('/admin/plugins?message=Plugin deactivated successfully');
            } else {
                error_log("Failed to deactivate plugin $pluginName in controller");
                return Response::redirect('/admin/plugins?message=Error deactivating plugin');
            }
        }

        public function isPluginActive($pluginName)
        {
            return isset($this->activePlugins[$pluginName]);
        }
    }