<?php

    namespace App\Core;

    class EventDispatcher
    {
        private static $listeners = [];

        public static function listen($event, $listener)
        {
            if (!isset(self::$listeners[$event])) {
                self::$listeners[$event] = [];
            }

            self::$listeners[$event][] = $listener;
        }

        public static function dispatch($event, $payload = null)
        {
            $eventName = is_object($event) ? get_class($event) : $event;

            if (!isset(self::$listeners[$eventName])) {
                return;
            }

            foreach (self::$listeners[$eventName] as $listener) {
                if (is_callable($listener)) {
                    call_user_func($listener, $payload);
                } elseif (is_string($listener) && class_exists($listener)) {
                    $instance = new $listener();
                    if (method_exists($instance, 'handle')) {
                        $instance->handle($payload);
                    }
                }
            }
        }

        public static function remove($event, $listener = null)
        {
            if (!isset(self::$listeners[$event])) {
                return;
            }

            if (is_null($listener)) {
                unset(self::$listeners[$event]);
            } else {
                self::$listeners[$event] = array_filter(
                    self::$listeners[$event],
                    function ($registeredListener) use ($listener) {
                        return $registeredListener !== $listener;
                    }
                );
            }
        }

        public static function hasListeners($event)
        {
            return isset(self::$listeners[$event]) && !empty(self::$listeners[$event]);
        }

        public static function getListeners($event)
        {
            return self::$listeners[$event] ?? [];
        }
    }