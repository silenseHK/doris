<?php


namespace app\store\validate;


use think\Validate;

class CompanyValid extends Validate
{

    protected $rule = [
        'id' => 'require|>=:1',
        'title|分公司名' => 'require|max:30'
    ];

    protected $scene = [
        'add' => ['title'],
        'edit' => ['id', 'title'],
        'delete' => ['id']
    ];

}