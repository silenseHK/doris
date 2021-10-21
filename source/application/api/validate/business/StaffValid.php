<?php


namespace app\api\validate\business;


use think\Validate;

class StaffValid extends Validate
{

    protected $rule = [
        'id' => 'require|>=:1'
    ];

    protected $scene = [
        'collect' => ['id'],
    ];

}