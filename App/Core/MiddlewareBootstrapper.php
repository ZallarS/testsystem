<?php

    namespace App\Core;

    class MiddlewareBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $router = $app->getRouter();

            // Register global middleware
            $router->middleware([
                new \App\Middleware\SessionMiddleware(),
                new \App\Middleware\VerifyCsrfToken(),
                new \App\Middleware\SecurityHeadersMiddleware(),
            ]);
        }
    }