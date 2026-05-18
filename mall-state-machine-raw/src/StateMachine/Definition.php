<?php

declare(strict_types=1);

namespace App\StateMachine;

class Definition
{
    /** @var string[] */
    private array $places;

    /** @var Transition[] */
    private array $transitions;

    public function __construct(
        private readonly string $name,
        array $places,
        array $transitions,
        private readonly string $initialPlace,
    ) {
        $this->places      = $places;
        $this->transitions = $transitions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return string[] */
    public function getPlaces(): array
    {
        return $this->places;
    }

    /** @return Transition[] */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    public function getInitialPlace(): string
    {
        return $this->initialPlace;
    }
}
