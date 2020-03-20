<?php


namespace app\api\validate\user;


use think\Validate;

class Check extends Validate
{

    protected $rule = [
        'code' => 'require',
        'user_info' => 'require|array',
        'referee_id' => 'number|>=:0',
        'mobile' => 'require|mobile',
        'password' => 'require|min:6|password|confirm',
        'password_confirm' => 'require'
    ];

    protected $message = [
        'code' => '登陆code',
        'user_info' => '用户信息',
        'referee_id' => '推荐人',
        'mobile' => '手机号',
        'password' => '登陆密码'
    ];

    protected $scene = [
        'register' => ['code', 'user_info', 'referee_id', 'mobile', 'password', 'password_confirm']
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

    /**
     * 验证密码
     * @param $value
     * @return bool|string
     */
    protected function password($value){
        if(!preg_match('/[a-zA-Z]/', $value))return "密码需要为8-20位的字母和数字的组合";
        if(!preg_match('/[\d]/', $value))return "密码需要为8-20位的字母和数字的组合";
        if(!preg_match('/^[A-Za-z0-9]{8,20}$/', $value))return "密码需要为8-20位的字母和数字的组合";
        return true;
    }

}