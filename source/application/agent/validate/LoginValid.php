<?php


namespace app\agent\validate;


use think\Validate;

class LoginValid extends Validate
{

    protected $rule = [
        'username|账号' => 'require|mobile',
        'password|密码' => 'require|min:6',
        'verify|验证码' => 'require|captcha'
    ];

    protected $scene = [
        'login' => ['username', 'password']
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