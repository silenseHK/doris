<?php


namespace app\api\validate\user;


use think\Validate;

class TeamValidate extends Validate
{

    protected $rule = [
        'grade_id|代理级别' => "require|number|>=:0",
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1',
        'keywords|搜索关键字' => 'min:1',
        'user_id|用户id' => 'number|>=:1'
    ];

    protected $scene = [
        'member_list' => ['grade_id', 'page', 'size'],
        'normal_team_list' => ['grade_id', 'page', 'size', 'keywords', 'user_id'],
    ];

}