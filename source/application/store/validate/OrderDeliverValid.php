<?php


namespace app\store\validate;


use think\Validate;

class OrderDeliverValid extends Validate
{

    protected $rule = [
        'deliver_id' => 'require|number|>=:1',
        'order' => 'require|array',
        'order.express_id' => 'require|number|>=:1',
        'order.express_no' => 'require',
        'order.express_remark' => 'max:255'
    ];

    protected $scene = [
        'deliver' => ['deliver_id', 'order', 'order.express_id', 'order.express_no', 'order.express_remark']
    ];

}