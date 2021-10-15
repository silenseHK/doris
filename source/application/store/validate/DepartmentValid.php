<?php


namespace app\store\validate;


use think\Validate;

class DepartmentValid extends Validate
{

    protected $rule = [
        'id' => 'require|>=:1',
        'title|部门名' => 'require|max:30',
        'c_id|分公司' => 'require|>=:1',
    ];

    protected $scene = [
        'add' => ['title', 'c_id'],
        'edit' => ['id', 'title', 'c_id'],
        'delete' => ['id']
    ];

}