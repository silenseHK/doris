<?php

namespace app\api\validate\order;

use think\Validate;

class Checkout extends Validate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [

        // 商品id
        'goods_id' => [
            'require',
            'number',
            'gt' => 0
        ],

        // 购买数量
        'goods_num' => [
            'require',
            'number',
            'gt' => 0
        ],

        // 商品sku_id
        'goods_sku_id' => [
            'require',
        ],

//        // 购物车id集
//        'cart_ids' => [
//            'require',
//        ],

        'order_type' => 'require|number|in:0,10,20,30',
        'page' => 'number|>=:1',
        'size' => 'number|>=:1',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'buyNow' => ['goods_id', 'goods_num', 'goods_sku_id'],
//        'cart' => ['cart_ids'],
        'agent_sale_order' => ['order_type', 'page', 'size']
    ];

}
