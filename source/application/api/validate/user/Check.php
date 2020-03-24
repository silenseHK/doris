<?php


namespace app\api\validate\user;


use think\Validate;

class Check extends Validate
{

    protected $rule = [
        'code|登陆code' => 'require',
        'user_info|用户信息' => 'require',
        'referee_id|推荐人' => 'number|>=:0',
        'mobile|手机号' => 'require|mobile',
        'password|登陆密码' => 'require|min:6|password|confirm',
        'password_confirm' => 'require',
        'verify_code|验证码' => 'require',
        'wxapp_id' => 'require',
        'code_type|验证码类型' => 'require|in:10',
        'goods_id|商品id' => 'require|number|>=:0'
    ];

    protected $scene = [
        'register' => ['code', 'user_info', 'referee_id', 'mobile', 'password', 'password_confirm', 'wxapp_id'],
        'send_verify_code' => ['mobile', 'wxapp_id', 'code_type'],
        'login' => ['mobile', 'password'],
        'goods_send_data' => ['goods_id'],
        'bind_mobile' => ['mobile', 'verify_code']
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
        if(!preg_match('/[a-zA-Z]/', $value))return "密码需要为8-20位字母和数字的组合";
        if(!preg_match('/[\d]/', $value))return "密码需要为8-20位字母和数字的组合";
        if(!preg_match('/^[A-Za-z0-9]{8,20}$/', $value))return "密码需要为8-20位字母和数字的组合";
        return true;
    }

}