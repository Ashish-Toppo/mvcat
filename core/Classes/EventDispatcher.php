<?php

namespace Core\Classes;

class EventDispatcher
{
    /**
     * Map of events to their listeners
     * @var array
     */
    private static array $listeners = [];

    /**
     * Register events and their listeners
     *
     * @param array $mapping
     */
    public static function register(array $mapping): void
    {
        self::$listeners = array_merge(self::$listeners, $mapping);
    }

    /**
     * Dispatch an event
     *
     * @param object $event The event instance
     */
    public static function dispatch(object $event): void
    {
        $eventName = get_class($event);

        if (!isset(self::$listeners[$eventName])) {
            return;
        }

        foreach (self::$listeners[$eventName] as $listenerClass) {
            if (class_exists($listenerClass)) {
                $listener = new $listenerClass();
                if (method_exists($listener, 'handle')) {
                    $listener->handle($event);
                } else {
                    error_log("Event Listener {$listenerClass} does not have a handle method.");
                }
            } else {
                error_log("Event Listener {$listenerClass} not found.");
            }
        }
    }
}
