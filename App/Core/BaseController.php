<?php

    namespace App\Core;

    abstract class BaseController
    {
        protected $container;
        protected $validator;
        protected $response;
        protected $viewRenderer;

        public function __construct(Container $container = null)
        {
            $this->container = $container ?: Application::getContainer();
            $this->validator = new Validator();
            $this->response = new Response();
            $this->viewRenderer = new ViewRenderer();
        }

        protected function validateRequest(array $rules, array $data = null)
        {
            $data = $data ?? ($_POST + $_GET);
            $errors = $this->validator->validate($data, $rules);

            if (!empty($errors)) {
                return $this->jsonResponse(['errors' => $errors], 422);
            }

            return null;
        }

        protected function jsonResponse($data, $statusCode = 200)
        {
            return Response::json($data, $statusCode);
        }

        protected function viewResponse($view, $data = [], $statusCode = 200)
        {
            return Response::view($view, $data, $statusCode);
        }

        protected function redirectResponse($url, $statusCode = 302)
        {
            return Response::redirect($url, $statusCode);
        }

        protected function handleException(\Exception $e, $message = 'An error occurred')
        {
            error_log("Controller error: " . $e->getMessage());

            if ($_ENV['APP_ENV'] === 'production') {
                return $this->jsonResponse(['error' => $message], 500);
            } else {
                return $this->jsonResponse([
                    'error' => $message,
                    'debug' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        protected function authorize($permission, $user = null)
        {
            $user = $user ?: \App\Core\User::get();

            if (!\App\Core\Permission::can($permission, $user['roles'] ?? [])) {
                throw new \Exception("Access denied");
            }
        }
    }