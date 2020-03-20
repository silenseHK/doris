<?php

namespace app\common\service\order;

use app\common\library\helper;
use app\common\model\User as UserModel;
use app\common\model\Order as OrderModel;
use app\common\model\user\PointsLog;

/**
 * 已完成订单结算服务类
 * Class Complete
 * @package app\common\service\order
 */
class Complete
{
    /* @var OrderModel $model */
    private $model;

    /* @var UserModel $model */
    private $UserModel;

    public function __construct()
    {
        $this->model = new OrderModel;
        $this->UserModel = new UserModel;
    }

    /**
     * 执行订单结算
     * @param $orderList
     * @return bool
     * @throws \Exception
     */
    public function settled($orderList)
    {
        // 订单id集
        $orderIds = helper::getArrayColumn($orderList, 'order_id');
        // 累积用户实际消费金额
        $this->setIncUserExpend($orderList);
        // 处理订单赠送的积分
        $this->setGiftPointsBonus($orderList);
        // 将订单设置为已结算
        $this->model->onBatchUpdate($orderIds, ['is_settled' => 1]);
        return true;
    }

    /**
     * 处理订单赠送的积分
     * @param $orderList
     * @return bool
     * @throws \Exception
     */
    private function setGiftPointsBonus($orderList)
    {
        // 计算用户所得积分
        $userData = [];
        $logData = [];
        foreach ($orderList as $order) {
            if ($order['points_bonus'] <= 0) continue;
            // 计算用户所得积分
            !isset($userData[$order['user_id']]) && $userData[$order['user_id']] = 0;
            $userData[$order['user_id']] += $order['points_bonus'];
            // 整理用户积分变动明细
            $logData[] = [
                'user_id' => $order['user_id'],
                'value' => $order['points_bonus'],
                'describe' => "订单赠送：{$order['order_no']}",
                'wxapp_id' => $order['wxapp_id'],
            ];
        }
        if (!empty($userData)) {
            // 累积到会员表记录
            $this->UserModel->onBatchIncPoints($userData);
            // 批量新增积分明细记录
            (new PointsLog)->onBatchAdd($logData);
        }
        return true;
    }

    /**
     * 累积用户实际消费金额
     * @param $orderList
     * @return bool
     * @throws \Exception
     */
    private function setIncUserExpend($orderList)
    {
        // 计算并累积实际消费金额(需减去售后退款的金额)
        $userData = [];
        foreach ($orderList as $order) {
            // 订单实际支付金额
            $expendMoney = $order['pay_price'];
            // 减去订单退款的金额
            foreach ($order['goods'] as $goods) {
                if (
                    !empty($goods['refund'])
                    && $goods['refund']['type']['value'] == 10      // 售后类型：退货退款
                    && $goods['refund']['is_agree']['value'] == 10  // 商家审核：已同意
                    && $goods['refund']['status']['value'] == 20    // 售后单状态：已完成
                ) {
                    $expendMoney -= $goods['refund']['refund_money'];
                }
            }
            !isset($userData[$order['user_id']]) && $userData[$order['user_id']] = 0.00;
            $expendMoney > 0 && $userData[$order['user_id']] += $expendMoney;
        }
        // 累积到会员表记录
        $this->UserModel->onBatchIncExpendMoney($userData);
        return true;
    }

}