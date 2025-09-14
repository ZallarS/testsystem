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
                'title' => 'Управление пользователями',
                'activeMenu' => 'users'
            ]);
        }

        public function edit($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            return $this->view('admin/users/edit', [
                'user' => $user,
                'title' => 'Редактирование пользователя',
                'activeMenu' => 'users',
                'roles' => ['user', 'moderator', 'admin']
            ]);
        }

        public function delete($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            if ($this->userModel->delete($id)) {
                return Response::redirect('/admin/users?message=Пользователь успешно удален');
            } else {
                return Response::redirect('/admin/users?error=Ошибка при удалении пользователя');
            }
        }

        public function update($id)
        {
            $user = $this->userModel->find($id);

            if (!$user) {
                return Response::redirect('/admin/users?error=Пользователь не найден');
            }

            $data = [
                'name' => $_POST['name'] ?? $user['name'],
                'email' => $_POST['email'] ?? $user['email'],
                'role' => $_POST['role'] ?? $user['role']
            ];

            if ($this->userModel->update($id, $data)) {
                return Response::redirect('/admin/users?message=Пользователь успешно обновлен');
            } else {
                return Response::redirect('/admin/users/edit/' . $id . '?error=Ошибка при обновлении пользователя');
            }
        }
    }