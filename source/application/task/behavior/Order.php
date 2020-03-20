<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\Setting;
use app\task\model\Order as OrderModel;
use app\task\model\OrderGoods as OrderGoodsModel;
use app\task\model\UserCoupon as UserCouponModel;
use app\task\model\dealer\Order as DealerOrderModel;

use app\common\enum\OrderType as OrderTypeEnum;
use app\common\service\wechat\wow\Order as WowService;
use app\common\service\order\Complete as OrderCompleteService;
use app\common\library\helper;

/**
 * 订单行为管理
 * Class Order
 * @package app\task\behavior
 */
class Order
{
    /* @var \app\task\model\Order $model */
    private $model;

    /**
     * 执行函数
     * @param $model
     * @return bool
     */
    public function run($model)
    {
        if (!$model instanceof OrderModel) {
            return new OrderModel and false;
        }
        $this->model = $model;
        if (!$model::$wxapp_id) {
            return false;
        }
        if (!Cache::has("__task_space__order__{$model::$wxapp_id}")) {
            // 获取商城交易设置
            $config = Setting::getItem('trade');
            $this->model->transaction(function () use ($config) {
                // 未支付订单自动关闭
                $this->close($config['order']['close_days']);
                // 已发货订单自动确认收货
                $this->receive($config['order']['receive_days']);
                // 已完成订单结算
                $this->settled($config['order']['refund_days']);
            });
            Cache::set("__task_space__order__{$model::$wxapp_id}", time(), 3600);
        }
        return true;
    }

    /**
     * 未支付订单自动关闭
     * @param $closeDays
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    private function close($closeDays)
    {
        // 取消n天以前的的未付款订单
        if ($closeDays < 1) {
            return false;
        }
        // 截止时间
        $deadlineTime = time() - ((int)$closeDays * 86400);
        // 条件
        $filter = [
            'pay_status' => 10,
            'order_status' => 10,
            'create_time' => ['<=', $deadlineTime]
        ];
        // 查询截止时间未支付的订单
        $list = $this->model->getList($filter, ['goods', 'user']);
        $orderIds = helper::getArrayColumn($list, 'order_id');
        // 取消订单事件
        if (!empty($orderIds)) {
            $OrderGoodsModel = new OrderGoodsModel;
            foreach ($list as &$order) {
                // 回退商品库存
                $OrderGoodsModel->backGoodsStock($order['goods']);
                // 回退用户优惠券
                $order['coupon_id'] > 0 && UserCouponModel::setIsUse($order['coupon_id'], false);
                // 回退用户积分
                $describe = "订单取消：{$order['order_no']}";
                $order['points_num'] > 0 && $order['user']->setIncPoints($order['points_num'], $describe);
            }
            // 批量更新订单状态为已取消
            $this->model->onBatchUpdate($orderIds, ['order_status' => 20]);
        }
        // 记录日志
        $this->dologs('close', [
            'close_days' => (int)$closeDays,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
        return true;
    }

    /**
     * 已发货订单自动确认收货
     * @param $receiveDays
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function receive($receiveDays)
    {
        // 截止时间
        if ($receiveDays < 1) return false;
        $deadlineTime = time() - ((int)$receiveDays * 86400);
        // 条件
        $filter = [
            'pay_status' => 20,
            'delivery_status' => 20,
            'receipt_status' => 10,
            'delivery_time' => ['<=', $deadlineTime]
        ];
        // 订单id集
        $orderIds = $this->model->where($filter)->column('order_id');
        if (!empty($orderIds)) {
            // 更新订单收货状态
            $this->model->onBatchUpdate($orderIds, [
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
            // 批量处理已完成的订单
            $this->onReceiveCompleted($orderIds);
        }
        // 记录日志
        $this->dologs('receive', [
            'receive_days' => (int)$receiveDays,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
        return true;
    }

    /**
     * 已完成订单结算
     * @param $refundDays
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    private function settled($refundDays)
    {
        // 获取已完成的订单（未累积用户实际消费金额）
        // 条件1：订单状态：已完成
        // 条件2：超出售后期限
        // 条件3：is_settled 为 0
        // 截止时间
        $deadlineTime = time() - ((int)$refundDays * 86400);
        // 查询条件
        $filter = [
            'order_status' => 30,
            'receipt_time' => ['<=', $deadlineTime],     // 此处使用<=，用于兼容自动确认收货后
            'is_settled' => 0
        ];
        // 查询订单列表
        $orderList = $this->model->getList($filter, [
            'goods' => ['refund'],  // 用于计算售后退款金额
        ]);
        // 订单id集
        $orderIds = helper::getArrayColumn($orderList, 'order_id');
        // 订单结算服务
        !empty($orderIds) && (new OrderCompleteService)->settled($orderList);
        // 记录日志
        $this->dologs('settled', [
            'refund_days' => (int)$refundDays,
            'deadline_time' => $deadlineTime,
            'orderIds' => json_encode($orderIds),
        ]);
    }

    /**
     * 批量处理已完成的订单
     * @param $orderIds
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function onReceiveCompleted($orderIds)
    {
        // 获取已完成的订单列表
        $list = $this->model->getList(['order_id' => ['in', $orderIds]], [
            'goods' => ['refund'],  // 用于发放分销佣金
            'user', 'address', 'goods', 'express',  // 用于同步微信好物圈
        ]);
        if ($list->isEmpty()) {
            return false;
        }
        $model = $this->model;
        // 实例化好物圈订单服务类
        $WowService = new WowService($model::$wxapp_id);
        // 更新好物圈订单状态
        $WowService->update($list);
        // 批量发放分销订单佣金
        foreach ($list as $order) {
            DealerOrderModel::grantMoney($order, OrderTypeEnum::MASTER);
        }
        return true;
    }

    /**
     * 记录日志
     * @param $method
     * @param array $params
     * @return bool|int
     */
    private function dologs($method, $params = [])
    {
        $value = 'behavior Order --' . $method;
        foreach ($params as $key => $val)
            $value .= ' --' . $key . ' ' . $val;
        return log_write($value);
    }

}
