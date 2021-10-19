<?php


namespace app\store\validate;


use think\Validate;

class MatterValid extends Validate
{

    protected $rule = [
        'id' => 'require|number|>=:1',
        'title|分类名' => 'require|max:30',
        'status|状态' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['title', 'status'],
        'edit' => ['id', 'title', 'status'],
        'del' => ['id'],
        'done' => ['id'],
    ];

}