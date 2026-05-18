-- ============================================================
-- 商城状态机 - 状态定义 & 转换规则 (与 Symfony Workflow 配置对应)
-- 仅作为参考文档 & 初始化数据，实际流转由 Symfony Workflow 组件控制
-- ============================================================

USE `mall`;

-- ============================================================
-- 1. 商品 (product) 状态机
-- ============================================================
-- places: draft / pending_review / published / off_shelf / deleted
-- transitions:
--   draft       → submit_review  → pending_review
--   pending_review → approve     → published
--   pending_review → reject      → draft
--   published   → off_shelf      → off_shelf
--   off_shelf   → relist         → published
--   draft       → delete         → deleted
--   off_shelf   → delete         → deleted

SELECT '>>> 商品状态机' AS info;
-- 初始状态: draft

-- ============================================================
-- 2. 订单 (order) 状态机
-- ============================================================
-- places: pending / confirmed / processing / shipped / delivered / completed / cancelled / refunding
-- transitions:
--   pending     → confirm        → confirmed
--   confirmed   → process        → processing
--   processing  → ship           → shipped
--   shipped     → deliver        → delivered
--   delivered   → complete       → completed
--   pending     → cancel         → cancelled
--   confirmed   → cancel         → cancelled
--   delivered   → request_refund → refunding
--   refunding   → complete_refund → completed

SELECT '>>> 订单状态机' AS info;
-- 初始状态: pending

-- ============================================================
-- 3. 支付 (payment) 状态机
-- ============================================================
-- places: pending / processing / paid / failed / refunding / refunded / partial_refund
-- transitions:
--   pending     → pay            → processing
--   processing  → pay_success    → paid
--   processing  → pay_fail       → failed
--   failed      → retry_pay      → processing
--   paid        → start_refund   → refunding
--   refunding   → refund_success → refunded
--   paid        → partial_refund → partial_refund

SELECT '>>> 支付状态机' AS info;
-- 初始状态: pending

-- ============================================================
-- 4. 售后 (after_sale) 状态机
-- ============================================================
-- places: pending / approved / rejected / returning / returned / refunding / refunded / completed / closed
-- transitions:
--   pending     → approve               → approved
--   pending     → reject                → rejected
--   rejected    → reapply               → pending
--   approved    → start_return          → returning (仅退货/换货)
--   returning   → confirm_return        → returned
--   approved    → start_refund          → refunding (仅退款)
--   returned    → start_refund          → refunding
--   refunding   → refund_success        → refunded
--   refunded    → complete              → completed
--   pending     → close                 → closed
--   approved    → close                 → closed
--   rejected    → close                 → closed

SELECT '>>> 售后状态机' AS info;
-- 初始状态: pending

-- ============================================================
-- 5. 物流 (logistics) 状态机
-- ============================================================
-- places: pending / picking / packed / shipped / arrived / out_for_delivery / delivered / returned / exception
-- transitions:
--   pending         → start_pick    → picking
--   picking         → pack          → packed
--   packed          → ship_out      → shipped
--   shipped         → arrive        → arrived
--   arrived         → out_delivery  → out_for_delivery
--   out_for_delivery → sign         → delivered
--   shipped         → return_back   → returned
--   shipped         → mark_exception → exception
--   exception       → resolve       → shipped
--   picking         → mark_exception → exception
--   packed          → mark_exception → exception

SELECT '>>> 物流状态机' AS info;
-- 初始状态: pending

-- ============================================================
-- 初始化数据: 预置状态值 (可选，用于后台配置下拉等)
-- ============================================================
INSERT INTO `state_transition_log` (`entity_type`, `entity_id`, `from_state`, `to_state`, `transition`, `remark`)
VALUES
    ('system', 0, '', '', 'init', '状态机初始化 - Symfony Workflow 接管');
