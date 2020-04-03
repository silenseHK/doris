<?php


namespace app\store\validate;


use think\Validate;

class UserValid extends Validate
{

    protected $rule = [
        'user_id' => 'require|number|>=:1',
        'mobile' => 'require|mobile',
        'exchange_user_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'exchange_team' => ['user_id', 'exchange_user_id'],  ##转换团队
    ];

    /**
     * 验证手机号
     * @param $value
     * @return bool|string
     */
    protected function mobile($value){
        if(preg_match("/^1[345789]\d{9}$/", $value)){
            return true;
        }else{
            return "手机号格式错误";
        }
    }

}