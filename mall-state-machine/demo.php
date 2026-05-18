<?php

/**
 * 商城状态机演示脚本
 * 展示 商品/下单/支付/售后/物流 五个模块的状态流转
 *
 * 运行: php demo.php
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mall\Entity\AfterSale;
use Mall\Entity\Logistics;
use Mall\Entity\Order;
use Mall\Entity\Payment;
use Mall\Entity\Product;
use Mall\EventSubscriber\WorkflowEventSubscriber;
use Mall\Workflow\StateMachineManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

function buildWorkflow(string $name, array $places, array $transitions, string $initial): StateMachine
{
    $builder = new DefinitionBuilder($places);
    foreach ($transitions as $t => [$from, $to]) {
        // 状态机模式下，多 from 需要拆分为独立 Transition（OR 语义）
        foreach ((array) $from as $singleFrom) {
            $builder->addTransition(new Transition($t, $singleFrom, $to));
        }
    }
    $builder->setInitialPlaces($initial);
    $definition = $builder->build();

    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new WorkflowEventSubscriber());

    return new StateMachine(
        $definition,
        new MethodMarkingStore(true, 'state'),
        $dispatcher,
        $name
    );
}

// ---- 初始化状态机管理器 ----

$manager = new StateMachineManager();

$manager->addWorkflow('product', buildWorkflow('product',
    ['draft', 'pending_review', 'published', 'off_shelf', 'deleted'],
    [
        'submit_review' => ['draft', 'pending_review'],
        'approve'       => ['pending_review', 'published'],
        'reject'        => ['pending_review', 'draft'],
        'off_shelf'     => ['published', 'off_shelf'],
        'relist'        => ['off_shelf', 'published'],
        'delete'        => [['draft', 'off_shelf'], 'deleted'],
    ],
    'draft'
));

$manager->addWorkflow('order', buildWorkflow('order',
    ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunding'],
    [
        'confirm'         => ['pending', 'confirmed'],
        'process'         => ['confirmed', 'processing'],
        'ship'            => ['processing', 'shipped'],
        'deliver'         => ['shipped', 'delivered'],
        'complete'        => ['delivered', 'completed'],
        'cancel'          => [['pending', 'confirmed'], 'cancelled'],
        'request_refund'  => ['delivered', 'refunding'],
        'complete_refund' => ['refunding', 'completed'],
    ],
    'pending'
));

$manager->addWorkflow('payment', buildWorkflow('payment',
    ['pending', 'processing', 'paid', 'failed', 'refunding', 'refunded', 'partial_refund'],
    [
        'pay'             => ['pending', 'processing'],
        'pay_success'     => ['processing', 'paid'],
        'pay_fail'        => ['processing', 'failed'],
        'retry_pay'       => ['failed', 'processing'],
        'start_refund'    => ['paid', 'refunding'],
        'refund_success'  => ['refunding', 'refunded'],
        'partial_refund'  => ['paid', 'partial_refund'],
    ],
    'pending'
));

$manager->addWorkflow('after_sale', buildWorkflow('after_sale',
    ['pending', 'approved', 'rejected', 'returning', 'returned', 'refunding', 'refunded', 'completed', 'closed'],
    [
        'approve'        => ['pending', 'approved'],
        'reject'         => ['pending', 'rejected'],
        'reapply'        => ['rejected', 'pending'],
        'start_return'   => ['approved', 'returning'],
        'confirm_return' => ['returning', 'returned'],
        'start_refund'   => [['approved', 'returned'], 'refunding'],
        'refund_success' => ['refunding', 'refunded'],
        'complete'       => ['refunded', 'completed'],
        'close'          => [['pending', 'approved', 'rejected'], 'closed'],
    ],
    'pending'
));

$manager->addWorkflow('logistics', buildWorkflow('logistics',
    ['pending', 'picking', 'packed', 'shipped', 'arrived', 'out_for_delivery', 'delivered', 'returned', 'exception'],
    [
        'start_pick'      => ['pending', 'picking'],
        'pack'            => ['picking', 'packed'],
        'ship_out'        => ['packed', 'shipped'],
        'arrive'          => ['shipped', 'arrived'],
        'out_delivery'    => ['arrived', 'out_for_delivery'],
        'sign'            => ['out_for_delivery', 'delivered'],
        'return_back'     => ['shipped', 'returned'],
        'mark_exception'  => [['shipped', 'picking', 'packed'], 'exception'],
        'resolve'         => ['exception', 'shipped'],
    ],
    'pending'
));

// ---- 演示 ----

echo str_repeat('=', 60) . "\n";
echo "  商城状态机演示 (Symfony Workflow)\n";
echo str_repeat('=', 60) . "\n\n";

// 1. 商品
echo "【1. 商品状态机】\n";
$product = new Product();
$product->setTitle('iPhone 15 Pro')->setSku('SKU001')->setPrice(8999.00)->setStock(100);
echo "  初始: {$product->getState()}\n";
$manager->apply($product, 'submit_review');
echo "  提交审核 → {$product->getState()}\n";
$manager->apply($product, 'approve');
echo "  审核通过 → {$product->getState()}\n";
$manager->apply($product, 'off_shelf');
echo "  下架    → {$product->getState()}\n";
echo "  可用转换: ";
foreach ($manager->getAvailableTransitions($product) as $t) {
    echo $t->getName() . ' ';
}
echo "\n\n";

// 2. 订单
echo "【2. 订单状态机】\n";
$order = new Order();
$order->setOrderNo('ORD202605180001')->setBuyerId(1001)->setTotalAmount(8999.00);
echo "  初始: {$order->getState()}\n";
$manager->apply($order, 'confirm');
echo "  确认  → {$order->getState()}\n";
$manager->apply($order, 'process');
echo "  处理中 → {$order->getState()}\n";
$manager->apply($order, 'ship');
echo "  发货  → {$order->getState()}\n";
$manager->apply($order, 'deliver');
echo "  送达  → {$order->getState()}\n";
$manager->apply($order, 'complete');
echo "  完成  → {$order->getState()}\n";
echo "\n";

// 3. 支付
echo "【3. 支付状态机】\n";
$payment = new Payment();
$payment->setPaymentNo('PAY202605180001')->setOrderId(1)->setAmount(8999.00)->setChannel('alipay');
echo "  初始: {$payment->getState()}\n";
$manager->apply($payment, 'pay');
echo "  支付中 → {$payment->getState()}\n";
$manager->apply($payment, 'pay_success');
echo "  成功  → {$payment->getState()}\n";
$manager->apply($payment, 'start_refund');
echo "  退款中 → {$payment->getState()}\n";
$manager->apply($payment, 'refund_success');
echo "  已退款 → {$payment->getState()}\n";
echo "\n";

// 4. 售后
echo "【4. 售后状态机】\n";
$afterSale = new AfterSale();
$afterSale->setAfterSaleNo('AS202605180001')->setOrderId(1)->setType('return')->setReason('质量问题')->setRefundAmount(8999.00);
echo "  初始: {$afterSale->getState()}\n";
$manager->apply($afterSale, 'approve');
echo "  通过  → {$afterSale->getState()}\n";
$manager->apply($afterSale, 'start_return');
echo "  退货中 → {$afterSale->getState()}\n";
$manager->apply($afterSale, 'confirm_return');
echo "  已退回 → {$afterSale->getState()}\n";
$manager->apply($afterSale, 'start_refund');
echo "  退款中 → {$afterSale->getState()}\n";
$manager->apply($afterSale, 'refund_success');
echo "  已退款 → {$afterSale->getState()}\n";
$manager->apply($afterSale, 'complete');
echo "  完成  → {$afterSale->getState()}\n";
echo "\n";

// 5. 物流
echo "【5. 物流状态机】\n";
$logistics = new Logistics();
$logistics->setLogisticsNo('LOG202605180001')->setOrderId(1)->setCarrier('sf')->setTrackingNo('SF1234567890');
echo "  初始: {$logistics->getState()}\n";
$manager->apply($logistics, 'start_pick');
echo "  拣货  → {$logistics->getState()}\n";
$manager->apply($logistics, 'pack');
echo "  打包  → {$logistics->getState()}\n";
$manager->apply($logistics, 'ship_out');
echo "  出库  → {$logistics->getState()}\n";
$manager->apply($logistics, 'arrive');
echo "  到达  → {$logistics->getState()}\n";
$manager->apply($logistics, 'out_delivery');
echo "  派送中 → {$logistics->getState()}\n";
$manager->apply($logistics, 'sign');
echo "  签收  → {$logistics->getState()}\n";
echo "\n";

// 6. 异常场景
echo "【6. 异常场景 - 取消订单】\n";
$order2 = new Order();
$order2->setOrderNo('ORD202605180002')->setBuyerId(1002)->setTotalAmount(199.00);
echo "  初始: {$order2->getState()}\n";
$manager->apply($order2, 'cancel');
echo "  取消  → {$order2->getState()}\n";
echo "\n";

echo "【7. 异常场景 - 支付失败重试】\n";
$payment2 = new Payment();
$payment2->setPaymentNo('PAY202605180002')->setOrderId(2)->setAmount(199.00)->setChannel('wechat');
echo "  初始: {$payment2->getState()}\n";
$manager->apply($payment2, 'pay');
echo "  支付中 → {$payment2->getState()}\n";
$manager->apply($payment2, 'pay_fail');
echo "  失败  → {$payment2->getState()}\n";
$manager->apply($payment2, 'retry_pay');
echo "  重试  → {$payment2->getState()}\n";
$manager->apply($payment2, 'pay_success');
echo "  成功  → {$payment2->getState()}\n";
echo "\n";

// 状态日志
echo str_repeat('=', 60) . "\n";
echo "  所有状态流转日志\n";
echo str_repeat('=', 60) . "\n";
foreach ([$product, $order, $payment, $afterSale, $logistics, $order2, $payment2] as $entity) {
    $label = str_pad((new ReflectionClass($entity))->getShortName(), 12);
    echo "  {$label} state={$entity->getState()}  log=" . json_encode($entity->getStateLog(), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n演示完成。\n";
