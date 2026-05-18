# Mall State Machine

商城状态机系统，覆盖 **商品 / 下单 / 支付 / 售后 / 物流** 五大业务模块。

提供两套实现：**原生 PHP**（零依赖）和 **Symfony Workflow** 组件版。

## 项目结构

```
demo1/
├── databases/                                 # SQL 建表 & 状态参考
│   ├── mall_schema.sql                        #   6 张业务表 DDL
│   └── mall_state_machine.sql                 #   状态 & 转换规则说明
│
├── mall-state-machine-raw/                    # 原生 PHP 实现 ★ 推荐
│   ├── demo.php                               #   演示脚本 (8 个场景)
│   └── src/
│       ├── StateMachine/                      #   状态机引擎
│       │   ├── StateMachine.php               #     核心: can / apply / getEnabledTransitions
│       │   ├── Definition.php                 #     定义: places + transitions
│       │   ├── Transition.php                 #     转换: name + froms + tos
│       │   ├── Event/
│       │   │   ├── EventDispatcher.php        #     PSR 风格事件分发
│       │   │   └── TransitionEvent.php        #     转换事件
│       │   └── Exception/
│       │       └── TransitionNotAllowedException.php
│       ├── Entity/                            #   实体 (5 个)
│       │   ├── Product.php                    #     商品
│       │   ├── Order.php                      #     订单
│       │   ├── Payment.php                    #     支付
│       │   ├── AfterSale.php                  #     售后
│       │   └── Logistics.php                  #     物流
│       └── Workflow/                          #   工作流定义 & 管理器
│           ├── StateMachineManager.php        #     门面: 自动注册 / 事件日志 / 时间戳
│           ├── ProductWorkflow.php
│           ├── OrderWorkflow.php
│           ├── PaymentWorkflow.php
│           ├── AfterSaleWorkflow.php
│           └── LogisticsWorkflow.php
│
└── mall-state-machine/                        # Symfony Workflow 版
    ├── demo.php                               #   演示脚本
    ├── composer.json                          #   依赖: symfony/workflow ^6.4
    ├── config/workflows.yaml                  #   YAML 工作流配置
    └── src/
        ├── Entity/                            #   实体 (5 个)
        ├── Workflow/StateMachineManager.php   #   门面封装
        └── EventSubscriber/
            └── WorkflowEventSubscriber.php    #   事件监听 & 日志
```

## 状态流转

五模块状态图与转换表 — 两套实现共享完全相同的状态和转换定义。

### 商品 (Product)

```
draft ──→ pending_review ──→ published ⇄ off_shelf
  │                            │
  └──── delete ────────────────┘
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
               │  ↑          │
               ↓  └─ retry   └── partial_refund
             failed
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
              ┌─ start_return ──→ returning ──→ returned ──┐
pending ──→ approved ─────────────────────────────────────→ start_refund ──→ refunding
  │  ↓        │                                                              │
  │ reject ──→ reapply → (back to pending)               refund_success ──→ refunded ──→ completed
  └── close
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
              │           │           │
              └─ exception ───────────┤  resolve → shipped
                          │           │
                          └─ exception              return_back → returned
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

### 原生版（推荐，零依赖）

```bash
cd mall-state-machine-raw
php demo.php
```

支持 PHP >= 8.1，无需 Composer、无需安装任何依赖。

### Symfony 版

```bash
cd mall-state-machine
composer install
php demo.php
```

需要 PHP >= 8.1 + Composer。

## 用法对比

### 原生版

```php
use App\Workflow\StateMachineManager;
use App\Entity\Product;

$manager = new StateMachineManager();               // 自动注册全部 5 个工作流

$product = new Product();
$product->setTitle('商品A')->setSku('SKU001');

$manager->can($product, 'submit_review');           // bool
$manager->apply($product, 'submit_review');         // draft → pending_review
$manager->getEnabledTransitions($product);          // Transition[]
$manager->getEventLog();                            // 全局事件日志
```

### Symfony 版

```php
use Mall\Workflow\StateMachineManager;
use Mall\Entity\Product;

$manager = new StateMachineManager();
// 需手动注册工作流 (或通过 Framework Bundle YAML 配置)

$manager->can($product, 'submit_review');           // bool
$manager->apply($product, 'submit_review');         // draft → pending_review
$manager->getEnabledTransitions($product);          // Transition[]
```

## 引擎对比

| | 原生版 | Symfony 版 |
|---|---|---|
| 依赖 | 无 | symfony/workflow ^6.4 |
| 配置方式 | PHP 类常量 | YAML / PHP |
| 事件系统 | 内置 EventDispatcher | Symfony EventDispatcher |
| 状态日志 | 实体 `appendStateLog()` + 管理器全局日志 | EventSubscriber 监听 |
| 多-from 语义 | 自动拆分为 OR | 需手动拆分 (Bundle 层自动) |
| 运行 | `php demo.php` | `composer install && php demo.php` |

## 数据库

SQL 文件位于 `databases/`：

| 表 | 说明 |
|---|---|
| `product` | 商品表，`state` + `state_log`(JSON) |
| `mall_order` | 订单表，含 `placed_at` / `paid_at` / `shipped_at` 等时间节点 |
| `payment` | 支付表，含 `channel` 支付渠道 |
| `after_sale` | 售后表，`type` 区分退款/退货/换货 |
| `logistics` | 物流表，含 `carrier` 承运商 + `tracking_no` 运单号 |
| `state_transition_log` | 独立状态变更日志表，`entity_type` + `entity_id` 关联任意模块 |

```bash
mysql -u root -p < databases/mall_schema.sql
```

## 关键设计

**状态机多-from OR 语义**：工作流定义中的多 `from` 写法（如 `delete: [draft, off_shelf] → deleted`）表示"从 draft 或 off_shelf 都可执行 delete"。引擎层面会自动将此类定义拆分为多条独立的 Transition 实现 OR 语义，避免 AND 陷阱。

**事件日志双通道**：实体自身携带 `stateLog`（JSON 数组，跟随实体持久化），管理器同时维护全局 `eventLog`（内存级别，用于监控/审计）。

**非法转换保护**：对未定义的转换调用 `apply()` 会抛出 `TransitionNotAllowedException`，明确指出当前状态和尝试的转换名称。

**与数据库解耦**：状态机引擎操作的是实体对象（`getState()` / `setState()` 接口），不感知持久化层。可直接集成 Doctrine ORM、Eloquent 或手写 SQL。

## License

[MIT](LICENSE)
