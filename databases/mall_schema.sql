-- ============================================================
-- 商城状态机 - 数据库表结构
-- 涵盖: 商品 / 下单 / 支付 / 售后 / 物流 五大模块
-- ============================================================

CREATE DATABASE IF NOT EXISTS `mall` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mall`;

-- ----------------------------
-- 商品表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `product` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`         VARCHAR(255)    NOT NULL DEFAULT '' COMMENT '商品标题',
    `sku`           VARCHAR(64)     NOT NULL DEFAULT '' COMMENT 'SKU编码',
    `price`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT '售价',
    `stock`         INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '库存',
    `state`         VARCHAR(32)     NOT NULL DEFAULT 'draft' COMMENT '状态机当前状态',
    `state_log`     JSON            NULL COMMENT '状态变更日志',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_sku` (`sku`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品表';

-- ----------------------------
-- 订单表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mall_order` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_no`          VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '订单号',
    `buyer_id`          BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '买家ID',
    `total_amount`      DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT '订单总额',
    `pay_amount`        DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT '实付金额',
    `state`             VARCHAR(32)     NOT NULL DEFAULT 'pending' COMMENT '状态机当前状态',
    `state_log`         JSON            NULL COMMENT '状态变更日志',
    `placed_at`         DATETIME        NULL COMMENT '下单时间',
    `paid_at`           DATETIME        NULL COMMENT '支付时间',
    `shipped_at`        DATETIME        NULL COMMENT '发货时间',
    `delivered_at`      DATETIME        NULL COMMENT '签收时间',
    `completed_at`      DATETIME        NULL COMMENT '完成时间',
    `cancelled_at`      DATETIME        NULL COMMENT '取消时间',
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order_no` (`order_no`),
    KEY `idx_buyer_id` (`buyer_id`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表';

-- ----------------------------
-- 支付表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `payment` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_no`        VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '支付单号',
    `order_id`          BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联订单ID',
    `amount`            DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT '支付金额',
    `channel`           VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '支付渠道: alipay/wechat/unionpay',
    `state`             VARCHAR(32)     NOT NULL DEFAULT 'pending' COMMENT '状态机当前状态',
    `state_log`         JSON            NULL COMMENT '状态变更日志',
    `paid_at`           DATETIME        NULL COMMENT '支付成功时间',
    `refunded_at`       DATETIME        NULL COMMENT '退款完成时间',
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payment_no` (`payment_no`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付表';

-- ----------------------------
-- 售后表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `after_sale` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `after_sale_no`     VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '售后单号',
    `order_id`          BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联订单ID',
    `type`              VARCHAR(16)     NOT NULL DEFAULT 'refund' COMMENT '售后类型: refund/return/exchange',
    `reason`            VARCHAR(500)    NOT NULL DEFAULT '' COMMENT '售后原因',
    `refund_amount`     DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT '退款金额',
    `state`             VARCHAR(32)     NOT NULL DEFAULT 'pending' COMMENT '状态机当前状态',
    `state_log`         JSON            NULL COMMENT '状态变更日志',
    `approved_at`       DATETIME        NULL COMMENT '审核通过时间',
    `returned_at`       DATETIME        NULL COMMENT '退货入库时间',
    `refunded_at`       DATETIME        NULL COMMENT '退款完成时间',
    `completed_at`      DATETIME        NULL COMMENT '售后完成时间',
    `closed_at`         DATETIME        NULL COMMENT '售后关闭时间',
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_after_sale_no` (`after_sale_no`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='售后表';

-- ----------------------------
-- 物流表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `logistics` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `logistics_no`      VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '物流单号',
    `order_id`          BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联订单ID',
    `carrier`           VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '承运商: sf/yto/zto/st/ems/jd',
    `tracking_no`       VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '快递单号',
    `state`             VARCHAR(32)     NOT NULL DEFAULT 'pending' COMMENT '状态机当前状态',
    `state_log`         JSON            NULL COMMENT '状态变更日志',
    `picked_at`         DATETIME        NULL COMMENT '拣货完成时间',
    `packed_at`         DATETIME        NULL COMMENT '打包完成时间',
    `shipped_at`        DATETIME        NULL COMMENT '出库时间',
    `delivered_at`      DATETIME        NULL COMMENT '签收时间',
    `returned_at`       DATETIME        NULL COMMENT '退回时间',
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_logistics_no` (`logistics_no`),
    UNIQUE KEY `uk_tracking_no` (`tracking_no`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物流表';

-- ----------------------------
-- 状态变更日志表 (所有模块共用)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `state_transition_log` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entity_type`   VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '实体类型: product/order/payment/after_sale/logistics',
    `entity_id`     BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `from_state`    VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '变更前状态',
    `to_state`      VARCHAR(32)     NOT NULL DEFAULT '' COMMENT '变更后状态',
    `transition`    VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '触发的转换名称',
    `operator_id`   BIGINT UNSIGNED NULL COMMENT '操作人ID',
    `operator_name` VARCHAR(64)     NULL COMMENT '操作人名称',
    `remark`        VARCHAR(500)    NULL COMMENT '备注',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_entity` (`entity_type`, `entity_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='状态变更日志表';
