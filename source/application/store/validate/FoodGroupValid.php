<?php


namespace app\store\validate;


use think\Validate;

class FoodGroupValid extends Validate
{

    protected $rule = [
        'img_id' => 'require|number|>=:1',
        'max_bmi' => 'require|number|>=:0',
        'min_bmi' => 'require|number|>=:0',
        'id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['img_id', 'max_bmi', 'min_bmi'],
        'edit' => ['id', 'img_id', 'max_bmi', 'min_bmi'],
        'del' => ['id'],
        'info' => ['id'],
    ];

}