<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;

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

        return $this->view('admin/dashboard', [
            'title' => 'Административная панель',
            'activeMenu' => 'admin_dashboard',
        ]);
    }

    public function settings()
    {
        return $this->view('admin/settings', [
            'title' => 'Настройки системы',
            'activeMenu' => 'admin_settings'
        ]);
    }

}