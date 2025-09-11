<?php

    namespace App\Controllers\Admin;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Core\Application;

    class PluginsController extends Controller {
        private $pluginManager;

        public function __construct()
        {
            parent::__construct();

            // Получаем PluginManager через Application
            $container = Application::getContainer();
            if (!$container) {
                throw new \Exception('Application container not initialized');
            }

            $this->pluginManager = $container->get('plugin_manager');
            if (!$this->pluginManager) {
                throw new \Exception('PluginManager not found in container');
            }
        }

        public function index() {
            $plugins = $this->pluginManager->getPlugins();
            $activePlugins = $this->pluginManager->getActivePlugins();

            return $this->view('admin/plugins/index', [
                'plugins' => $plugins,
                'activePlugins' => $activePlugins,
                'title' => 'Управление плагинами'
            ]);
        }

        public function activate($pluginName) {
            try {
                if ($this->pluginManager->activatePlugin($pluginName)) {
                    return Response::redirect('/admin/plugins?message=Плагин успешно активирован');
                }
            } catch (\Exception $e) {
                return Response::redirect('/admin/plugins?error=' . urlencode($e->getMessage()));
            }
        }

        public function deactivate($pluginName) {
            try {
                if ($this->pluginManager->deactivatePlugin($pluginName)) {
                    return Response::redirect('/admin/plugins?message=Плагин успешно деактивирован');
                }
            } catch (\Exception $e) {
                return Response::redirect('/admin/plugins?error=' . urlencode($e->getMessage()));
            }
        }

        public function details($pluginName) {
            $plugin = $this->pluginManager->getPlugin($pluginName);

            if (!$plugin) {
                return Response::redirect('/admin/plugins?error=Плагин не найден');
            }

            return $this->view('admin/plugins/details', [
                'plugin' => $plugin,
                'isActive' => $this->pluginManager->isPluginActive($pluginName),
                'title' => 'Информация о плагине: ' . $plugin->getName()
            ]);
        }
    }