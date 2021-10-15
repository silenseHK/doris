<?php


namespace app\store\validate;


use think\Validate;

class AgentValid extends Validate
{

    protected $rule = [
        'user_id' => 'require|number|>=:1|unique:agent,user_id',
        'mobile' => 'require|mobile',
        'password' => 'require|min:6|max:30',
        'agent_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['user_id', 'mobile', 'password'],
        'edit' => ['user_id', 'mobile', 'agent_id'],
        'edit_status' => ['agent_id'],
        'edit_pwd' => ['agent_id', 'password']
    ];

    /**
     * 验证手机号
     * @param $value
     * @return bool|string
     */
    protected function mobile($value){
        if(preg_match("/^1[3456789]\d{9}$/", $value)){
            return true;
        }else{
            return "手机号格式错误";
        }
    }

}