<?php

declare(strict_types=1);

namespace Mall\Workflow;

use Mall\Entity\AfterSale;
use Mall\Entity\Logistics;
use Mall\Entity\Order;
use Mall\Entity\Payment;
use Mall\Entity\Product;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * 状态机管理器 — 对 Symfony Workflow 做统一门面封装
 */
class StateMachineManager
{
    private array $workflows = [];

    public function addWorkflow(string $name, WorkflowInterface $workflow): void
    {
        $this->workflows[$name] = $workflow;
    }

    public function getWorkflow(string $name): WorkflowInterface
    {
        if (!isset($this->workflows[$name])) {
            throw new \InvalidArgumentException("Workflow '{$name}' not registered.");
        }
        return $this->workflows[$name];
    }

    // ---- 便捷方法 ----

    public function can(object $subject, string $transition): bool
    {
        return $this->resolve($subject)->can($subject, $transition);
    }

    public function apply(object $subject, string $transition, array $context = []): void
    {
        $workflow = $this->resolve($subject);

        if (!$workflow->can($subject, $transition)) {
            $from = method_exists($subject, 'getState') ? $subject->getState() : '?';
            $cls = get_class($subject);
            throw new \LogicException(
                "Transition '{$transition}' not allowed from '{$from}' for {$cls}"
            );
        }

        $workflow->apply($subject, $transition, $context);
    }

    public function getAvailableTransitions(object $subject): array
    {
        return $this->resolve($subject)->getEnabledTransitions($subject);
    }

    public function getState(object $subject): string
    {
        $places = $this->resolve($subject)->getMarking($subject)->getPlaces();
        return (string) reset($places);
    }

    // ---- 实体 -> 工作流映射 ----

    private function resolve(object $subject): WorkflowInterface
    {
        return match (true) {
            $subject instanceof Product    => $this->getWorkflow('product'),
            $subject instanceof Order      => $this->getWorkflow('order'),
            $subject instanceof Payment    => $this->getWorkflow('payment'),
            $subject instanceof AfterSale  => $this->getWorkflow('after_sale'),
            $subject instanceof Logistics  => $this->getWorkflow('logistics'),
            default => throw new \InvalidArgumentException(
                'No workflow for ' . get_class($subject)
            ),
        };
    }
}
