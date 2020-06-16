<?php


namespace app\store\validate;


use think\Validate;

class LecturerValid extends Validate
{

    protected $rule = [
        'name' => 'require|max:30',
        'avatar' => 'require|number|>=:1',
        'desc' => 'max:255',
        'label' => 'array',
        'lecturer_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['name', 'avatar', 'desc', 'label'],
        'edit' => ['lecturer_id', 'name', 'avatar', 'desc', 'label'],
    ];

}