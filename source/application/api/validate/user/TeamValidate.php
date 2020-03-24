<?php


namespace app\api\validate\user;


use think\Validate;

class TeamValidate extends Validate
{

    protected $rule = [
        'grade_id|代理级别' => "require|number|>=:0",
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1'
    ];

    protected $scene = [
        'member_list' => ['grade_id', 'page', 'size'],
    ];

}