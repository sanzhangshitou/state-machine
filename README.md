# Mall State Machine

基于 Symfony Workflow 组件的商城状态机系统，覆盖 **商品 / 下单 / 支付 / 售后 / 物流** 五大业务模块。

## 架构概览

```
databases/                         # SQL 层
├── mall_schema.sql                #   6 张表结构
└── mall_state_machine.sql         #   状态 & 转换规则参考

mall-state-machine/                # PHP 层
├── src/Entity/                    #   实体 (5 个)
├── src/Workflow/                  #   状态机管理器
├── src/EventSubscriber/           #   事件监听 & 日志
├── config/workflows.yaml          #   YAML 配置
└── demo.php                       #   可运行演示
```

## 状态流转

### 商品 (Product)

```
draft ──→ pending_review ──→ published ⇄ off_shelf
  │                            │
  └────────── delete ──────────┘
```

| Transition | From | To |
|---|---|---|
| `submit_review` | draft | pending_review |
| `approve` | pending_review | published |
| `reject` | pending_review | draft |
| `off_shelf` | published | off_shelf |
| `relist` | off_shelf | published |
| `delete` | draft, off_shelf | deleted |

### 订单 (Order)

```
pending ──→ confirmed ──→ processing ──→ shipped ──→ delivered ──→ completed
  │            │                                       │
  └── cancel ──┘                                       └── request_refund ──→ refunding ──→ completed
```

| Transition | From | To |
|---|---|---|
| `confirm` | pending | confirmed |
| `process` | confirmed | processing |
| `ship` | processing | shipped |
| `deliver` | shipped | delivered |
| `complete` | delivered | completed |
| `cancel` | pending, confirmed | cancelled |
| `request_refund` | delivered | refunding |
| `complete_refund` | refunding | completed |

### 支付 (Payment)

```
pending ──→ processing ──→ paid ──→ refunding ──→ refunded
               │             │
               └── failed ───┘  (retry_pay) → back to processing
               └── paid ──────────────────────→ partial_refund
```

| Transition | From | To |
|---|---|---|
| `pay` | pending | processing |
| `pay_success` | processing | paid |
| `pay_fail` | processing | failed |
| `retry_pay` | failed | processing |
| `start_refund` | paid | refunding |
| `refund_success` | refunding | refunded |
| `partial_refund` | paid | partial_refund |

### 售后 (AfterSale)

```
              ┌─→ start_return ──→ returning ──→ confirm_return ──→ returned ──┐
pending ──→ approved ────────────────────────────────────────────────────────→ start_refund ──→ refunding
  │           │                                                                                       │
  ├──→ reject ──→ reapply → pending                                        refund_success ──→ refunded ──→ completed
  └──→ close
```

| Transition | From | To |
|---|---|---|
| `approve` | pending | approved |
| `reject` | pending | rejected |
| `reapply` | rejected | pending |
| `start_return` | approved | returning |
| `confirm_return` | returning | returned |
| `start_refund` | approved, returned | refunding |
| `refund_success` | refunding | refunded |
| `complete` | refunded | completed |
| `close` | pending, approved, rejected | closed |

### 物流 (Logistics)

```
pending ──→ picking ──→ packed ──→ shipped ──→ arrived ──→ out_for_delivery ──→ delivered
                           │           │
                           └── exception ──→ resolve ──→ shipped
                           └── return_back ──→ returned
```

| Transition | From | To |
|---|---|---|
| `start_pick` | pending | picking |
| `pack` | picking | packed |
| `ship_out` | packed | shipped |
| `arrive` | shipped | arrived |
| `out_delivery` | arrived | out_for_delivery |
| `sign` | out_for_delivery | delivered |
| `return_back` | shipped | returned |
| `mark_exception` | shipped, picking, packed | exception |
| `resolve` | exception | shipped |

## 快速开始

### 环境要求

- PHP >= 8.1
- Composer

### 安装

```bash
cd mall-state-machine
composer install
```

### 运行演示

```bash
php demo.php
```

演示包含 7 个场景：正向流转、取消订单、支付失败重试，输出每个实体的状态日志。

### 基础用法

```php
use Mall\Workflow\StateMachineManager;
use Mall\Entity\Product;

$manager = new StateMachineManager();
// 注册工作流...

$product = new Product();
$product->setTitle('商品A')->setSku('SKU001');

$manager->can($product, 'submit_review');     // true/false
$manager->apply($product, 'submit_review');   // draft → pending_review
$manager->getState($product);                 // "pending_review"
$manager->getAvailableTransitions($product);  // [approve, reject]
```

## 数据库

SQL 文件位于 `databases/` 目录：

| 表 | 说明 |
|---|---|
| `product` | 商品表，含 `state` + `state_log`(JSON) |
| `mall_order` | 订单表，含时间节点字段 |
| `payment` | 支付表，含支付渠道 |
| `after_sale` | 售后表，支持退款/退货/换货 |
| `logistics` | 物流表，含承运商和运单号 |
| `state_transition_log` | 独立状态变更日志表，所有模块共用 |

导入方式：

```bash
mysql -u root -p < databases/mall_schema.sql
mysql -u root -p < databases/mall_state_machine.sql
```

## 关键设计说明

**状态机多-from 语义**：Symfony Workflow 状态机模式下，Transition 的多个 `from` 是 AND 语义（要求同时处于所有状态）。本项目在 `buildWorkflow()` 中自动拆分为多个同名 Transition 实现 OR 语义。若直接使用 YAML 配置 + Symfony Framework Bundle，Bundle 层已自动处理此拆分。

**事件监听**：`WorkflowEventSubscriber` 监听 `entered` 事件（状态变更后），自动记录 `state_log` 并更新时间戳字段。若要添加业务逻辑，在此订阅器中扩展即可。

## License

[MIT](LICENSE)
