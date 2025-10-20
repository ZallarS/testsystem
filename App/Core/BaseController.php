<?php

    namespace App\Core;

    abstract class BaseController
    {
        protected $container;
        protected $request;
        protected $response;
        protected $validator;
        protected $viewRenderer;

        public function __construct(
            Container $container,
            Request $request,
            Response $response,
            Validator $validator,
            ViewRenderer $viewRenderer
        ) {
            $this->container = $container;
            $this->request = $request;
            $this->response = $response;
            $this->validator = $validator;
            $this->viewRenderer = $viewRenderer;
        }

        protected function view($viewPath, $data = [], $statusCode = 200)
        {
            // Automatically add CSRF token for forms
            if (!isset($data['csrfToken'])) {
                $data['csrfToken'] = CSRF::generateToken();
            }

            return $this->response->view($viewPath, $data, $statusCode);
        }

        protected function json($data, $statusCode = 200)
        {
            return $this->response->json($data, $statusCode);
        }

        protected function redirect($url, $statusCode = 302)
        {
            return $this->response->redirect($url, $statusCode);
        }

        protected function validate(array $rules, array $messages = [])
        {
            $data = $this->request->all();
            $errors = $this->validator->validate($data, $rules, $messages);

            if (!empty($errors)) {
                return $this->json(['errors' => $errors], 422);
            }

            return null;
        }

        protected function authorize($permission)
        {
            if (!Permission::can($permission)) {
                throw new \Exception("Access denied");
            }
        }

        protected function getUser()
        {
            return User::get();
        }
    }