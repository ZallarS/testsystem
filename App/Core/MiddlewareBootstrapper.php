<?php

    namespace App\Core;

    class MiddlewareBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $router = $app->getRouter();

            // Register global middleware - ВАЖНО: SessionMiddleware должен быть ПЕРВЫМ
            $router->middleware([
                new \App\Middleware\SessionMiddleware(),    // ДОЛЖЕН БЫТЬ ПЕРВЫМ
                new \App\Middleware\VerifyCsrfToken(),
                new \App\Middleware\SecurityHeadersMiddleware(),
            ]);

            error_log("Middleware order: Session -> VerifyCsrfToken -> SecurityHeaders");
        }
    }