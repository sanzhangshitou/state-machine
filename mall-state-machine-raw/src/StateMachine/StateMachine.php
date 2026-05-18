<?php

declare(strict_types=1);

namespace App\StateMachine;

use App\StateMachine\Event\EventDispatcher;
use App\StateMachine\Event\TransitionEvent;

class StateMachine
{
    /** @var array<string, Transition[]> */
    private array $transitionsByName = [];

    public function __construct(
        private readonly Definition $definition,
        private readonly ?EventDispatcher $dispatcher = null,
    ) {
        foreach ($definition->getTransitions() as $t) {
            $this->transitionsByName[$t->getName()][] = $t;
        }
    }

    public function getDefinition(): Definition
    {
        return $this->definition;
    }

    public function can(object $subject, string $transitionName): bool
    {
        $current = $this->readState($subject);
        foreach ($this->transitionsByName[$transitionName] ?? [] as $t) {
            if (in_array($current, $t->getFroms(), true)) {
                return true;
            }
        }
        return false;
    }

    public function apply(object $subject, string $transitionName, array $context = []): void
    {
        if (!$this->can($subject, $transitionName)) {
            $current = $this->readState($subject);
            throw new Exception\TransitionNotAllowedException(
                sprintf(
                    "Transition '%s' is not allowed from '%s' for workflow '%s'.",
                    $transitionName,
                    $current,
                    $this->definition->getName(),
                ),
            );
        }

        $current = $this->readState($subject);
        $transition = $this->resolveTransition($transitionName, $current);
        $toState = $transition->getTos()[0];

        $this->writeState($subject, $toState);

        // 统一使用 state_machine.transition 事件名，workflow 名存在 event 里
        $this->dispatcher?->dispatch(
            new TransitionEvent(
                $subject, $current, $toState,
                $this->definition->getName(),
                $transition, $context,
            ),
            'state_machine.transition',
        );
    }

    /** @return Transition[] */
    public function getEnabledTransitions(object $subject): array
    {
        $current = $this->readState($subject);
        $enabled = [];
        foreach ($this->definition->getTransitions() as $t) {
            if (in_array($current, $t->getFroms(), true)) {
                $enabled[] = $t;
            }
        }
        return $enabled;
    }

    private function readState(object $subject): string
    {
        return $subject->{'getState'}();
    }

    private function writeState(object $subject, string $state): void
    {
        $subject->{'setState'}($state);
    }

    private function resolveTransition(string $name, string $current): Transition
    {
        foreach ($this->transitionsByName[$name] ?? [] as $t) {
            if (in_array($current, $t->getFroms(), true)) {
                return $t;
            }
        }
        throw new \LogicException('No matching transition found.');
    }
}
