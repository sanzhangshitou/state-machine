<?php

declare(strict_types=1);

namespace App\StateMachine;

class Transition
{
    /** @var string[] */
    private array $froms;

    /** @var string[] */
    private array $tos;

    public function __construct(
        private readonly string $name,
        string|array $froms,
        string|array $tos,
    ) {
        $this->froms = (array) $froms;
        $this->tos   = (array) $tos;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return string[] */
    public function getFroms(): array
    {
        return $this->froms;
    }

    /** @return string[] */
    public function getTos(): array
    {
        return $this->tos;
    }
}
