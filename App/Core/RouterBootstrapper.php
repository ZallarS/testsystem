<?php

    namespace App\Core;

    class RouterBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $router = $app->getContainer()->get('router');
            $app->setRouter($router);

            // Load routes
            $this->loadRoutes($router);
        }

        private function loadRoutes(Router $router)
        {
            $webRoutes = BASE_PATH . '/routes/web.php';
            if (file_exists($webRoutes)) {
                require $webRoutes;
            }

            $apiRoutes = BASE_PATH . '/routes/api.php';
            if (file_exists($apiRoutes)) {
                require $apiRoutes;
            }
        }
    }