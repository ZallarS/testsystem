<?php

    namespace App\Core;

    interface BootstrapperInterface
    {
        public function bootstrap(Application $app);
    }