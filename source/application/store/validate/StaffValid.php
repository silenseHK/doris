<?php


namespace app\store\validate;


use think\Validate;

class StaffValid extends Validate
{

    protected $rule = [
        'id' => 'require|>=:1',
        'title|员工名' => 'require|max:20',
        'a_id|部门' =>  'number|>=:1',
        'c_id|公司' =>  'number|>=:1',
        'is_expert|是否专家' => 'number|>=:0',
        'role_id|角色' => 'number|>=:0',
        'pwd|登陆密码' => 'require|max:30',
        'account|登陆账号' => 'require|max:30',
        'status|状态' => 'require|>=:1',
    ];

    protected $scene = [
        'add' => ['title', 'a_id', 'c_id', 'role_id', 'is_expert', 'pwd', 'account', 'status'],
        'edit' => ['id', 'title', 'a_id', 'c_id', 'role_id', 'is_expert', 'account', 'status'],
        'delete' => ['id'],
    ];

}