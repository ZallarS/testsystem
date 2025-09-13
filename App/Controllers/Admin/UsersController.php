<?php

    namespace App\Controllers\Admin;

    use App\Core\Controller;
    use App\Core\Response;
    use App\Models\User as UserModel;

    class UsersController extends Controller
    {
        private $userModel;

        public function __construct()
        {
            parent::__construct();
            $this->userModel = new UserModel();
        }

        public function index()
        {
            $users = $this->userModel->all();

            return $this->view('admin/users/index', [
                'users' => $users,
                'title' => 'User Management'
            ]);
        }

        public function edit($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::make('User not found', 404);
            }

            return $this->view('admin/users/edit', [
                'user' => $user,
                'title' => 'Edit User',
                'roles' => ['user', 'moderator', 'admin'] // Простой массив ролей
            ]);
        }

        public function update($id)
        {
            try {
                \App\Core\CSRF::validateToken($_POST['csrf_token'] ?? '');
            } catch (\Exception $e) {
                return Response::redirect('/admin/users?error=CSRF validation failed');
            }

            $user = $this->userModel->find($id);
            $allowedRoles = ['user', 'moderator', 'admin'];
            $role = $_POST['role'] ?? $user['role'];

            if (!in_array($role, $allowedRoles)) {
                return Response::redirect('/admin/users?error=Invalid role');
            }

            if (!$user) {
                return Response::make('User not found', 404);
            }

            $data = [
                'name' => $_POST['name'] ?? $user['name'],
                'email' => $_POST['email'] ?? $user['email'],
                'role' => $_POST['role'] ?? $user['role']
            ];

            if ($this->userModel->update($id, $data)) {
                return Response::redirect('/admin/users?message=User updated successfully');
            } else {
                return Response::redirect('/admin/users/edit/' . $id . '?error=Failed to update user');
            }
        }
    }