<?php


namespace app\api\validate\user;


use think\Validate;

class OrderDeliverValidate extends Validate
{

    protected $rule = [
        'address_id|收货地址' => 'require|number|>=:1',
        'goods_num|出货数' => 'require|number|>=:1',
        'deliver_type|出货类型' => 'require|number|in:10,20',
        'goods_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'apply' => ['address_id', 'goods_num', 'deliver_type', 'goods_id'],
        'count_freight' => ['address_id', 'goods_num', 'goods_id']
    ];

}