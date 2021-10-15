<?php


namespace app\agent\logic;

use think\captcha\Captcha;

class MockLogic extends BaseLogic
{

    public function userInfo($agent){
        return [
            'avatar' => $agent['user']['avatarUrl'],
            'permissions' => ['admin'],
            'username' => $agent['user']['nickName'],
            'agent' => $agent
        ];
    }

    public function verify(){
        $config =    [
            // 验证码字体大小
            'fontSize'    =>    20,
            // 验证码位数
            'length'      =>    4,
            // 关闭验证码杂点
            'useNoise'    =>    true,

            'imageW' => 160,

            'imageH' => 50,
            'useZh' => false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

}