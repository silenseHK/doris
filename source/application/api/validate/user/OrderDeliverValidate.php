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
        'goods_sku_id' => 'require|number|>=:1',
        'deliver_status|出货状态' => 'require|number|in:0,10,20,30,40',
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1',
        'deliver_id' => 'require|number|>=:1',
        'shop_id' => 'number|>=:0',
        'receiver_mobile' => 'mobile'
    ];

    protected $scene = [
        'apply' => ['address_id', 'goods_num', 'deliver_type', 'goods_sku_id', 'shop_id', 'receiver_mobile'],
        'count_freight' => ['address_id', 'goods_num', 'goods_id'],
        'order_list' => ['deliver_type', 'deliver_status', 'page', 'size'],
        'complete' => ['deliver_id'],
        'detail' => ['deliver_id'],
        'supply' => ['goods_sku_id', 'shop_id'],
        'express_info' => ['deliver_id']
    ];

    /**
     * 验证手机号
     * @param $value
     * @return bool|string
     */
    protected function mobile($value){
        if(preg_match("/^1[345789]\d{9}$/", $value)){
            return true;
        }else{
            return "手机号格式错误";
        }
    }

}