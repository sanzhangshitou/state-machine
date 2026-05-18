<?php

declare(strict_types=1);

namespace App\StateMachine\Event;

use App\StateMachine\Transition;

class TransitionEvent
{
    public function __construct(
        private readonly object $subject,
        private readonly string $fromState,
        private readonly string $toState,
        private readonly string $workflowName,
        private readonly Transition $transition,
        private readonly array $context = [],
    ) {
    }

    public function getSubject(): object { return $this->subject; }
    public function getFromState(): string { return $this->fromState; }
    public function getToState(): string { return $this->toState; }
    public function getWorkflowName(): string { return $this->workflowName; }
    public function getTransition(): Transition { return $this->transition; }
    public function getContext(): array { return $this->context; }
}
