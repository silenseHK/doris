<?php


namespace app\common\enum;


class DeliveryOrderStatus extends EnumBasics
{

    ##待发货
    const WAIT = 10;

    ##备货中
    const PREPARE = 20;

    ##已发货
    const SEND = 30;

    ##取消发货
    const CANCEL = 40;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::WAIT => [
                'name' => '待发货',
                'value' => self::WAIT,
            ],
            self::PREPARE => [
                'name' => '备货中',
                'value' => self::PREPARE,
            ],
            self::SEND => [
                'name' => '已发货',
                'value' => self::SEND
            ],
            self::CANCEL => [
                'name' => '已取消',
                'value' => self::CANCEL
            ],
        ];
    }
}