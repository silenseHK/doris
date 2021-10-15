<?php


namespace app\store\validate;


use think\Validate;

class DietitianValid extends Validate
{

    protected $rule = [
        'name' => 'require|min:2|max:30',
        'title' => 'require|min:2|max:30',
        'description' => 'require',
        'image_id' => 'require|number|>=:1',
        'sort' => 'require|number|>=:1|<=:99999',
        'dietitian_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['name', 'title', 'description', 'image_id'],
        'edit' => ['dietitian_id', 'name', 'title', 'description', 'image_id'],
        'edit_sort' => ['dietitian_id', 'sort'],
        'del' => ['dietitian_id']
    ];

}