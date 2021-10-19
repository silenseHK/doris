<?php


namespace app\api\validate\business;


use think\Validate;

class ReformValid extends Validate
{

    protected $rule = [
        'matter_id|所属问题' => 'require|number|>=:1',
        'desc|整改描述' => 'require',
        'amount|涉及金额' => 'require|>=:0',
        'department|整改部门' => 'max:30',
        'staff|整改人' => 'max:30',
    ];

    protected $scene = [
        'list' => ['matter_id'],
        'add' => ['matter_id', 'desc', 'amount', 'department', 'staff'],
    ];

}