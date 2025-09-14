<?php

    namespace App\Controllers\Admin;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Models\User;
    use App\Core\Application;

    class AdminController extends Controller
    {
        private $userModel;

        public function __construct()
        {
            parent::__construct();
            $this->userModel = new User();
        }

        public function dashboard()
        {
            // Получаем статистику
            $stats = $this->getStats();

            // Получаем последних пользователей
            $recentUsers = $this->userModel->all();
            $recentUsers = array_slice($recentUsers, -5, 5, true); // Последние 5 пользователей

            return $this->view('admin/dashboard', [
                'title' => 'Панель управления',
                'stats' => $stats,
                'recentUsers' => $recentUsers,
                'activeMenu' => 'dashboard'
            ]);
        }

        private function getStats()
        {
            // Здесь можно добавить реальную статистику из базы данных
            return [
                'totalUsers' => count($this->userModel->all()),
                'activeUsers' => rand(50, 100), // Заглушка
                'totalPlugins' => count(Application::getContainer()->get('plugin_manager')->getPlugins()),
                'activePlugins' => count(Application::getContainer()->get('plugin_manager')->getActivePlugins())
            ];
        }

        public function settings()
        {
            return $this->view('admin/settings', [
                'title' => 'Настройки системы',
                'activeMenu' => 'settings'
            ]);
        }
    }