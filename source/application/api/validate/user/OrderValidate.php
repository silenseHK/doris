<?php


namespace app\api\validate\user;


use think\Validate;

class OrderValidate extends Validate
{

    protected $rule = [
        'type' => 'require|number|in:10,20',
        'start_time' => 'date',
        'end_time' => 'date',
        'page' => 'number|>=:1',
        'size' => 'number|>=:1',
    ];

    protected $scene = [
        'income_list' => ['type', 'start_time', 'end_time', 'page', 'size']
    ];

}