<?php

declare(strict_types=1);

namespace App\StateMachine\Event;

class EventDispatcher
{
    /** @var array<string, callable[]> */
    private array $listeners = [];

    public function addListener(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event, string $eventName): void
    {
        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $listener($event);
        }
    }
}
