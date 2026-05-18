<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Entity\AfterSale;
use App\Entity\Logistics;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Product;
use App\StateMachine\Event\EventDispatcher;
use App\StateMachine\Event\TransitionEvent;
use App\StateMachine\StateMachine;
use App\StateMachine\Transition;

class StateMachineManager
{
    /** @var array<string, StateMachine> */
    private array $machines = [];

    private EventDispatcher $dispatcher;

    /** @var array<int, array> */
    private array $eventLog = [];

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addListener('state_machine.transition', $this->onTransition(...));

        $this->register(ProductWorkflow::NAME,    ProductWorkflow::build());
        $this->register(OrderWorkflow::NAME,      OrderWorkflow::build());
        $this->register(PaymentWorkflow::NAME,    PaymentWorkflow::build());
        $this->register(AfterSaleWorkflow::NAME,  AfterSaleWorkflow::build());
        $this->register(LogisticsWorkflow::NAME,  LogisticsWorkflow::build());
    }

    public function register(string $name, \App\StateMachine\Definition $definition): void
    {
        $this->machines[$name] = new StateMachine($definition, $this->dispatcher);
    }

    public function get(string $name): StateMachine
    {
        return $this->machines[$name]
            ?? throw new \InvalidArgumentException("Workflow '$name' not registered.");
    }

    // ---- 门面 ----

    public function can(object $subject, string $transition): bool
    {
        return $this->resolve($subject)->can($subject, $transition);
    }

    public function apply(object $subject, string $transition, array $context = []): void
    {
        $sm = $this->resolve($subject);
        $sm->apply($subject, $transition, $context);

        $newState = $subject->getState();
        if (method_exists($subject, 'appendStateLog')) {
            $subject->appendStateLog($newState);
        }
        $this->recordTimestamp($subject, $newState);
    }

    /** @return Transition[] */
    public function getEnabledTransitions(object $subject): array
    {
        return $this->resolve($subject)->getEnabledTransitions($subject);
    }

    /** @return array<int, array> */
    public function getEventLog(): array
    {
        return $this->eventLog;
    }

    // ---- 内部 ----

    private function resolve(object $subject): StateMachine
    {
        return match (true) {
            $subject instanceof Product   => $this->get(ProductWorkflow::NAME),
            $subject instanceof Order     => $this->get(OrderWorkflow::NAME),
            $subject instanceof Payment   => $this->get(PaymentWorkflow::NAME),
            $subject instanceof AfterSale => $this->get(AfterSaleWorkflow::NAME),
            $subject instanceof Logistics => $this->get(LogisticsWorkflow::NAME),
            default => throw new \InvalidArgumentException('No workflow for ' . get_class($subject)),
        };
    }

    private function onTransition(TransitionEvent $event): void
    {
        $this->eventLog[] = [
            'workflow'   => $event->getWorkflowName(),
            'entity'     => get_class($event->getSubject()),
            'transition' => $event->getTransition()->getName(),
            'from'       => $event->getFromState(),
            'to'         => $event->getToState(),
            'at'         => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function recordTimestamp(object $subject, string $state): void
    {
        $now = new \DateTimeImmutable();
        match (true) {
            $subject instanceof Order && $state === 'completed'   => $subject->setCompletedAt($now),
            $subject instanceof Order && $state === 'cancelled'   => $subject->setCancelledAt($now),
            $subject instanceof Payment && $state === 'paid'      => $subject->setPaidAt($now),
            $subject instanceof Payment && $state === 'refunded'  => $subject->setRefundedAt($now),
            $subject instanceof AfterSale && $state === 'completed' => $subject->setCompletedAt($now),
            $subject instanceof AfterSale && $state === 'closed'  => $subject->setClosedAt($now),
            $subject instanceof Logistics && $state === 'delivered' => $subject->setDeliveredAt($now),
            $subject instanceof Logistics && $state === 'returned' => $subject->setReturnedAt($now),
            default => null,
        };
    }
}
