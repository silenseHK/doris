<?php


namespace app\api\validate\user;


use think\Validate;

class OrderDeliverValidate extends Validate
{

    protected $rule = [
        'address_id|收货地址' => 'require|number|>=:1',
        'goods_num|出货数' => 'require|number|>=:1',
        'deliver_type|出货类型' => 'require|number|in:10,20',
        'goods_id' => 'require|number|>=:1',
        'deliver_status|出货状态' => 'require|number|in:0,10,20,30,40',
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1',
        'deliver_id' => 'require|number|>=:1',
        'shop_id' => 'number|>=:1'
    ];

    protected $scene = [
        'apply' => ['address_id', 'goods_num', 'deliver_type', 'goods_id', 'shop_id'],
        'count_freight' => ['address_id', 'goods_num', 'goods_id'],
        'order_list' => ['deliver_type', 'deliver_status', 'page', 'size'],
        'complete' => ['deliver_id'],
        'detail' => ['deliver_id']
    ];

}