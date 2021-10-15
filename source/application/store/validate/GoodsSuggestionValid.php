<?php


namespace app\store\validate;


use think\Validate;

class GoodsSuggestionValid extends Validate
{

    protected $rule = [
        'title' => 'require|unique:goods_suggestion,title|max:50',
        'goods_sku_id' => 'require|number|>=:1',
        'num' => 'require|number|>=:1',
        'sort' => 'require|number|>=:1',
        'suggestion_id' => 'require|number|>=:1',
        'field' => 'require',
        'value' => 'require',
        'image_id' => 'require|number|>=:1',
        'description' => 'require|min:1|max:255',
        'min_cycle' => 'require|number|>=:1',
        'max_cycle' => 'require|number|>=:1',
        'min_bmi' => 'require|number|>=:1',
        'max_bmi' => 'require|number|>=:1',
    ];

    protected $scene = [
        'add' => ['title', 'goods_sku_id', 'num', 'sort', 'image_id', 'description', 'min_cycle', 'max_cycle', 'min_bmi', 'max_bmi'],
        'edit' => ['suggestion_id', 'title', 'goods_sku_id', 'num', 'sort', 'image_id', 'description', 'min_cycle', 'max_cycle', 'min_bmi', 'max_bmi'],
        'edit_field' => ['suggestion_id', 'field', 'value']
    ];

}