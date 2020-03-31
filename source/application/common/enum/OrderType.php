<?php

namespace app\common\enum;

/**
 * 订单类型枚举类
 * Class OrderType
 * @package app\common\enum
 */
class OrderType extends EnumBasics
{
    // 商城订单
    const MASTER = 10;

    // 拼团订单
    const SHARING = 20;

    // 余额充值
    const RECHARGE = 100;

    ## 运费支付
    const FREIGHT = 30;

    ## 提货发货自提(用于核销)
    const SELF_DELIVERY = 40;

    /**
     * 获取订单类型值
     * @return array
     */
    public static function data()
    {
        return [
            self::MASTER => [
                'name' => '商城订单',
                'value' => self::MASTER,
            ],
            self::SHARING => [
                'name' => '拼团订单',
                'value' => self::SHARING,
            ],
            self::RECHARGE => [
                'name' => '余额充值',
                'value' => self::RECHARGE,
            ],
            self::FREIGHT => [
                'name' => '运费支付',
                'value' => self::FREIGHT,
            ],
            self::SELF_DELIVERY => [
                'name' => '提货发货自提',
                'value' => self::SELF_DELIVERY
            ]
        ];
    }

    /**
     * 获取订单类型名称
     * @return array
     */
    public static function getTypeName()
    {
        static $names = [];
        if (empty($names)) {
            foreach (self::data() as $item)
                $names[$item['value']] = $item['name'];
        }
        return $names;
    }

}