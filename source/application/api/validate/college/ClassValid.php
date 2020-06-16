<?php


namespace app\api\validate\college;


use think\Validate;

class ClassValid extends Validate
{

    protected $rule = [
        'class_id' => 'require|number|>=:1',
    ];

    protected $scene = [
        'class_detail' => ['class_id']
    ];

}