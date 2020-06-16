<?php


namespace app\common\enum\user;


use app\common\enum\EnumBasics;

class StockChangeScene extends EnumBasics
{
    ## 出货
    const SALE = 10;

    ## 后台操作
    const ADMIN = 20;

    ## 提货发货
    const SEND = 30;

    ## 补充库存
    const BUY = 40;

    ## 退款
    const REFUND = 50;

    public static function data(){
        return [
            self::SALE => [
                'value' => self::SALE,
                'text' => '出货'
            ],
            self::ADMIN => [
                'value' => self::ADMIN,
                'text' => '后台操作'
            ],
            self::SEND => [
                'value' => self::SEND,
                'text' => '提货发货'
            ],
            self::BUY => [
                'value' => self::BUY,
                'text' => '补充库存'
            ],
            self::REFUND => [
                'value' => self::REFUND,
                'text' => '用户退款'
            ],
        ];
    }

}