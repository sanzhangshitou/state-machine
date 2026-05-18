<?php

/**
 * 商城状态机演示脚本
 * 原生 PHP 实现，无第三方依赖
 *
 * 运行: php demo.php
 */

declare(strict_types=1);

// ---- 自动加载 ----

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__ . '/src/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

use App\Entity\AfterSale;
use App\Entity\Logistics;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Product;
use App\Workflow\StateMachineManager;

// ---- 初始化 ----

$manager = new StateMachineManager();

echo str_repeat('=', 60) . "\n";
echo "  商城状态机演示 (原生 PHP 实现)\n";
echo str_repeat('=', 60) . "\n\n";

// ================================================================
// 1. 商品状态机
// ================================================================
echo "【1. 商品状态机】\n";
$product = new Product();
$product->setTitle('iPhone 15 Pro')->setSku('SKU001')->setPrice(8999.00)->setStock(100);
printf("  初始: %s\n", $product->getState());

$manager->apply($product, 'submit_review');
printf("  提交审核 → %s\n", $product->getState());

$manager->apply($product, 'approve');
printf("  审核通过 → %s\n", $product->getState());

$manager->apply($product, 'off_shelf');
printf("  下架     → %s\n", $product->getState());

echo  "  可用转换: ";
foreach ($manager->getEnabledTransitions($product) as $t) {
    echo $t->getName() . ' ';
}
echo "\n\n";

// ================================================================
// 2. 订单状态机
// ================================================================
echo "【2. 订单状态机】\n";
$order = new Order();
$order->setOrderNo('ORD202605180001')->setBuyerId(1001)->setTotalAmount(8999.00);
printf("  初始: %s\n", $order->getState());

$manager->apply($order, 'confirm');
printf("  确认   → %s\n", $order->getState());

$manager->apply($order, 'process');
printf("  处理中 → %s\n", $order->getState());

$manager->apply($order, 'ship');
printf("  发货   → %s\n", $order->getState());

$manager->apply($order, 'deliver');
printf("  送达   → %s\n", $order->getState());

$manager->apply($order, 'complete');
printf("  完成   → %s\n", $order->getState());
echo "\n";

// ================================================================
// 3. 支付状态机
// ================================================================
echo "【3. 支付状态机】\n";
$payment = new Payment();
$payment->setPaymentNo('PAY202605180001')->setOrderId(1)->setAmount(8999.00)->setChannel('alipay');
printf("  初始: %s\n", $payment->getState());

$manager->apply($payment, 'pay');
printf("  支付中 → %s\n", $payment->getState());

$manager->apply($payment, 'pay_success');
printf("  成功   → %s\n", $payment->getState());

$manager->apply($payment, 'start_refund');
printf("  退款中 → %s\n", $payment->getState());

$manager->apply($payment, 'refund_success');
printf("  已退款 → %s\n", $payment->getState());
echo "\n";

// ================================================================
// 4. 售后状态机
// ================================================================
echo "【4. 售后状态机】\n";
$afterSale = new AfterSale();
$afterSale->setAfterSaleNo('AS202605180001')->setOrderId(1)
    ->setType('return')->setReason('质量问题')->setRefundAmount(8999.00);
printf("  初始: %s\n", $afterSale->getState());

$manager->apply($afterSale, 'approve');
printf("  通过   → %s\n", $afterSale->getState());

$manager->apply($afterSale, 'start_return');
printf("  退货中 → %s\n", $afterSale->getState());

$manager->apply($afterSale, 'confirm_return');
printf("  已退回 → %s\n", $afterSale->getState());

$manager->apply($afterSale, 'start_refund');
printf("  退款中 → %s\n", $afterSale->getState());

$manager->apply($afterSale, 'refund_success');
printf("  已退款 → %s\n", $afterSale->getState());

$manager->apply($afterSale, 'complete');
printf("  完成   → %s\n", $afterSale->getState());
echo "\n";

// ================================================================
// 5. 物流状态机
// ================================================================
echo "【5. 物流状态机】\n";
$logistics = new Logistics();
$logistics->setLogisticsNo('LOG202605180001')->setOrderId(1)
    ->setCarrier('sf')->setTrackingNo('SF1234567890');
printf("  初始: %s\n", $logistics->getState());

$manager->apply($logistics, 'start_pick');
printf("  拣货   → %s\n", $logistics->getState());

$manager->apply($logistics, 'pack');
printf("  打包   → %s\n", $logistics->getState());

$manager->apply($logistics, 'ship_out');
printf("  出库   → %s\n", $logistics->getState());

$manager->apply($logistics, 'arrive');
printf("  到达   → %s\n", $logistics->getState());

$manager->apply($logistics, 'out_delivery');
printf("  派送中 → %s\n", $logistics->getState());

$manager->apply($logistics, 'sign');
printf("  签收   → %s\n", $logistics->getState());
echo "\n";

// ================================================================
// 6. 异常场景 - 取消订单
// ================================================================
echo "【6. 异常场景 - 取消订单】\n";
$order2 = new Order();
$order2->setOrderNo('ORD202605180002')->setBuyerId(1002)->setTotalAmount(199.00);
printf("  初始: %s\n", $order2->getState());
$manager->apply($order2, 'cancel');
printf("  取消 → %s\n", $order2->getState());
echo "\n";

// ================================================================
// 7. 异常场景 - 支付失败重试
// ================================================================
echo "【7. 异常场景 - 支付失败重试】\n";
$payment2 = new Payment();
$payment2->setPaymentNo('PAY202605180002')->setOrderId(2)->setAmount(199.00)->setChannel('wechat');
printf("  初始: %s\n", $payment2->getState());
$manager->apply($payment2, 'pay');
printf("  支付中 → %s\n", $payment2->getState());
$manager->apply($payment2, 'pay_fail');
printf("  失败   → %s\n", $payment2->getState());
$manager->apply($payment2, 'retry_pay');
printf("  重试   → %s\n", $payment2->getState());
$manager->apply($payment2, 'pay_success');
printf("  成功   → %s\n", $payment2->getState());
echo "\n";

// ================================================================
// 8. 异常场景 - 物流异常恢复
// ================================================================
echo "【8. 异常场景 - 物流异常】\n";
$logistics2 = new Logistics();
$logistics2->setLogisticsNo('LOG202605180002')->setOrderId(3)
    ->setCarrier('yto')->setTrackingNo('YTO9876543210');
printf("  初始: %s\n", $logistics2->getState());
$manager->apply($logistics2, 'start_pick');
printf("  拣货   → %s\n", $logistics2->getState());
$manager->apply($logistics2, 'pack');
printf("  打包   → %s\n", $logistics2->getState());
$manager->apply($logistics2, 'ship_out');
printf("  出库   → %s\n", $logistics2->getState());
$manager->apply($logistics2, 'mark_exception');
printf("  异常   → %s\n", $logistics2->getState());
$manager->apply($logistics2, 'resolve');
printf("  恢复   → %s\n", $logistics2->getState());
echo "\n";

// ================================================================
// 汇总：状态日志
// ================================================================
echo str_repeat('=', 60) . "\n";
echo "  状态流转日志\n";
echo str_repeat('=', 60) . "\n";

$all = [
    'Product'   => $product,
    'Order1'    => $order,
    'Payment1'  => $payment,
    'AfterSale' => $afterSale,
    'Logistics1' => $logistics,
    'Order2'    => $order2,
    'Payment2'  => $payment2,
    'Logistics2' => $logistics2,
];

foreach ($all as $label => $entity) {
    printf(
        "  %-12s state=%-16s log=%s\n",
        $label,
        $entity->getState(),
        json_encode($entity->getStateLog(), JSON_UNESCAPED_UNICODE),
    );
}

echo "\n";

// 事件日志
echo str_repeat('=', 60) . "\n";
echo "  事件日志 (EventDispatcher)\n";
echo str_repeat('=', 60) . "\n";
foreach ($manager->getEventLog() as $i => $entry) {
    printf(
        "  #%d  %-12s %-20s  %-12s → %-16s  %s\n",
        $i + 1,
        basename(str_replace('\\', '/', $entry['entity'])),
        $entry['transition'],
        $entry['from'],
        $entry['to'],
        $entry['at'],
    );
}

// ================================================================
// 9. 非法转换测试
// ================================================================
echo "\n";
echo str_repeat('=', 60) . "\n";
echo "  非法转换测试\n";
echo str_repeat('=', 60) . "\n";

$testOrder = new Order();
$testOrder->setOrderNo('ORD202605180003')->setBuyerId(1003);
printf("  当前状态: %s\n", $testOrder->getState());
printf("  can('ship') = %s (expected: false)\n", $manager->can($testOrder, 'ship') ? 'true' : 'false');

try {
    $manager->apply($testOrder, 'ship');
} catch (\App\StateMachine\Exception\TransitionNotAllowedException $e) {
    printf("  捕获异常: %s\n", $e->getMessage());
}

echo "\n演示完成。\n";
