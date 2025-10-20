<?php

    namespace App\Core;

    abstract class EventServiceProvider
    {
        protected $listen = [];

        public function __construct()
        {
            $this->registerEvents();
        }

        public function registerEvents()
        {
            foreach ($this->listen as $event => $listeners) {
                foreach ($listeners as $listener) {
                    EventDispatcher::listen($event, $listener);
                }
            }
        }
    }