<?php

    namespace App\Core;

    class ContainerBootstrapper implements BootstrapperInterface
    {
        public function bootstrap(Application $app)
        {
            $container = new Container();
            $app->setContainer($container);

            // Register core services
            $container->singleton('router', Router::class);
            $container->singleton('request', function() {
                return Request::createFromGlobals();
            });
            $container->singleton('response', Response::class);
            $container->singleton('validator', Validator::class);
            $container->singleton('viewRenderer', ViewRenderer::class);

            // Register other services...
        }
    }