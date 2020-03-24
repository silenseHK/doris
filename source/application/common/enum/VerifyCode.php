<?php


namespace app\common\enum;


class VerifyCode extends EnumBasics
{

    const REGISTER = 10;

    public static function data(){
        return [
            self::REGISTER => [
                'text' => '注册验证码',
                'value' => self::REGISTER,
                'expire_time' => 10 * 60,  //10分钟有效
                'send_expire_time' => 2 * 60,  //2分钟内不能重复发送
                'days_num' => 100,  //一天最多发送五条
                'msg_type' => 'verify_code'
            ]
        ];
    }

}