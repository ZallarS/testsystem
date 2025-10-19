<?php

    namespace App\Validators;

    use App\Models\User;

    class UserValidator
    {
        private $userModel;

        public function __construct()
        {
            $this->userModel = new User();
        }

        public function getCreationRules()
        {
            return [
                'name' => 'required|string|min:2|max:50',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|strong_password',
                'confirm_password' => 'required|same:password',
                'roles' => 'array|valid_roles'
            ];
        }

        public function getUpdateRules()
        {
            return [
                'name' => 'required|string|min:2|max:50',
                'email' => 'required|email',
                'password' => 'sometimes|min:8|strong_password',
                'roles' => 'array|valid_roles'
            ];
        }

        public function validateUserId($id)
        {
            return is_numeric($id) && $id > 0;
        }

        public function validateUniqueEmail($email, $excludeUserId = null)
        {
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return true;
            }

            if ($excludeUserId && $user['id'] == $excludeUserId) {
                return true;
            }

            return false;
        }

        public function validateRoles($roles)
        {
            $validRoles = ['user', 'admin', 'moderator'];

            foreach ($roles as $role) {
                if (!in_array($role, $validRoles)) {
                    return false;
                }
            }

            return true;
        }
    }