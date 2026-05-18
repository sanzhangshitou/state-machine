<?php

declare(strict_types=1);

namespace Mall\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

/**
 * 监听所有状态机事件，记录状态变更日志
 */
class WorkflowEventSubscriber implements EventSubscriberInterface
{
    private array $logs = [];

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.product.entered'       => 'onProductEntered',
            'workflow.order.entered'         => 'onOrderEntered',
            'workflow.payment.entered'       => 'onPaymentEntered',
            'workflow.after_sale.entered'    => 'onAfterSaleEntered',
            'workflow.logistics.entered'     => 'onLogisticsEntered',

            'workflow.product.transition'  => 'onTransition',
            'workflow.order.transition'    => 'onTransition',
            'workflow.payment.transition'  => 'onTransition',
            'workflow.after_sale.transition' => 'onTransition',
            'workflow.logistics.transition'  => 'onTransition',
        ];
    }

    // ---- 进入新状态后更新实体时间戳 ----

    public function onProductEntered(EnteredEvent $event): void
    {
        $product = $event->getSubject();
        $places = $event->getMarking()->getPlaces();
        $place = (string) array_key_first($places);
        $this->appendStateLog($product, $place);
    }

    public function onOrderEntered(EnteredEvent $event): void
    {
        $order = $event->getSubject();
        $places = $event->getMarking()->getPlaces();
        $place = (string) array_key_first($places);
        $this->appendStateLog($order, $place);

        match ($place) {
            'completed'  => $order->setCompletedAt(new \DateTimeImmutable()),
            'cancelled'  => $order->setCancelledAt(new \DateTimeImmutable()),
            default       => null,
        };
    }

    public function onPaymentEntered(EnteredEvent $event): void
    {
        $payment = $event->getSubject();
        $places = $event->getMarking()->getPlaces();
        $place = (string) array_key_first($places);
        $this->appendStateLog($payment, $place);

        match ($place) {
            'paid'       => $payment->setPaidAt(new \DateTimeImmutable()),
            'refunded'   => $payment->setRefundedAt(new \DateTimeImmutable()),
            default       => null,
        };
    }

    public function onAfterSaleEntered(EnteredEvent $event): void
    {
        $entity = $event->getSubject();
        $places = $event->getMarking()->getPlaces();
        $place = (string) array_key_first($places);
        $this->appendStateLog($entity, $place);

        match ($place) {
            'completed'  => $entity->setCompletedAt(new \DateTimeImmutable()),
            'closed'     => $entity->setClosedAt(new \DateTimeImmutable()),
            default       => null,
        };
    }

    public function onLogisticsEntered(EnteredEvent $event): void
    {
        $entity = $event->getSubject();
        $places = $event->getMarking()->getPlaces();
        $place = (string) array_key_first($places);
        $this->appendStateLog($entity, $place);

        match ($place) {
            'delivered'  => $entity->setDeliveredAt(new \DateTimeImmutable()),
            'returned'   => $entity->setReturnedAt(new \DateTimeImmutable()),
            default       => null,
        };
    }

    // ---- 通用转换事件 ----

    public function onTransition(TransitionEvent $event): void
    {
        $context = $event->getContext();
        $from = implode(', ', array_keys($event->getMarking()->getPlaces()));
        $to = '';
        foreach ($event->getTransition()->getTos() as $toPlace) {
            $to = $toPlace;
        }

        $this->logs[] = [
            'entity'     => get_class($event->getSubject()),
            'transition' => $event->getTransition()->getName(),
            'from'       => $from,
            'to'         => $to,
            'context'    => $context,
            'at'         => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    private function appendStateLog(object $entity, string $place): void
    {
        if (!method_exists($entity, 'getStateLog')) {
            return;
        }

        $log = $entity->getStateLog() ?? [];
        $log[] = [
            'state' => $place,
            'at'    => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
        $entity->setStateLog($log);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function clearLogs(): void
    {
        $this->logs = [];
    }
}
