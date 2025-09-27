<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class AdminController extends Controller
{
    public function dashboard()
    {
        return $this->view('admin/dashboard', [
            'title' => 'Административная панель',
            'activeMenu' => 'dashboard'
        ]);
    }

    public function settings()
    {
        return $this->view('admin/settings', [
            'title' => 'Настройки системы',
            'activeMenu' => 'settings'
        ]);
    }
}