<?php


namespace app\api\validate\business;


use think\Validate;

class AdviceValid extends Validate
{

    protected $rule = [
        'advice|专家意见' => 'require',
        'desc|专家介绍' => 'require|max:255',
    ];

    protected $scene = [
        'add' => ['advice', 'desc']
    ];

}