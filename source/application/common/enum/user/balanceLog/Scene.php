<?php

namespace app\common\enum\user\balanceLog;

use app\common\enum\EnumBasics;

/**
 * 余额变动场景枚举类
 * Class Scene
 * @package app\common\enum\user\balanceLog
 */
class Scene extends EnumBasics
{
    // 用户充值
    const RECHARGE = 10;

    // 用户消费
    const CONSUME = 20;

    // 管理员操作
    const ADMIN = 30;

    // 订单退款
    const REFUND = 40;

    // 出货
    const SALE = 50;

    // 收到返利
    const REBATE = 60;

    // 付出返利
    const PAY_REBATE = 70;

    //申请提现
    const WITHDRAW = 80;

    //提现驳回
    const WITHDRAW_REFUSE = 90;

    /**
     * 获取订单类型值
     * @return array
     */
    public static function data()
    {
        return [
            self::RECHARGE => [
                'name' => '用户充值',
                'value' => self::RECHARGE,
                'describe' => '用户充值：%s',
            ],
            self::CONSUME => [
                'name' => '用户消费',
                'value' => self::CONSUME,
                'describe' => '用户消费：%s',
            ],
            self::ADMIN => [
                'name' => '管理员操作',
                'value' => self::ADMIN,
                'describe' => '后台管理员 [%s] 操作',
            ],
            self::REFUND => [
                'name' => '订单退款',
                'value' => self::REFUND,
                'describe' => '订单退款：%s',
            ],
            self::SALE => [
                'name' => '出货收益',
                'value' => self::SALE,
                'describe' => '销售商品：%s',
            ],
            self::REBATE => [
                'name' => '返利收益',
                'value' => self::REBATE,
                'describe' => '收到返利：%s'
            ],
            self::PAY_REBATE => [
                'name' => '返利支出',
                'value' => self::PAY_REBATE,
                'describe' => '支出返利：%s'
            ],
            self::WITHDRAW => [
                'name' => '申请提现',
                'value' => self::WITHDRAW,
                'describe' => '申请提现：%s'
            ],
            self::WITHDRAW_REFUSE => [
                'name' => '提现驳回',
                'value' => self::WITHDRAW_REFUSE,
                'describe' => '提现驳回：%s'
            ],
        ];
    }

}