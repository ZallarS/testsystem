<?php

namespace App\Core;

class Application
{
    private $container;
    private $router;
    private $bootstrappers = [];

    private static $instance;

    public function __construct()
    {
        self::$instance = $this;
        $this->registerBootstrappers();
        $this->bootstrap();
    }

    private function registerBootstrappers()
    {
        $this->bootstrappers = [
            new EnvironmentBootstrapper(),
            new ErrorHandlingBootstrapper(),
            new ContainerBootstrapper(),
            new RouterBootstrapper(),
            new MiddlewareBootstrapper(),
        ];
    }

    private function bootstrap()
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap($this);
        }
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();
    }

    public function handle(Request $request)
    {
        try {
            $method = $request->getMethod();
            $path = $request->getPathInfo();

            return $this->router->dispatch($method, $path);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleException(\Exception $e)
    {
        $handler = $this->container->get(ExceptionHandler::class);
        return $handler->render($e);
    }
}