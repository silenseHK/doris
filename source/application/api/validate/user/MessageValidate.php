<?php


namespace app\api\validate\user;


use think\Validate;

class MessageValidate extends Validate
{

    protected $rule = [
        'type' => 'require|number|in:10,20,30,40,50,60',
        'page' => 'number|>=:1',
        'size' => 'number|.=:1'
    ];

    protected $scene = [
        'lists' => ['type', 'page', 'size']
    ];

}