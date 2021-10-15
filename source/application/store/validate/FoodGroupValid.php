<?php


namespace app\store\validate;


use think\Validate;

class FoodGroupValid extends Validate
{

    protected $rule = [
        'imgs' => 'require|array',
        'max_bmi' => 'require|number|>=:0',
        'min_bmi' => 'require|number|>=:0',
        'id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['imgs', 'max_bmi', 'min_bmi'],
        'edit' => ['id', 'imgs', 'max_bmi', 'min_bmi'],
        'del' => ['id'],
        'info' => ['id'],
    ];

}