<?php

namespace app\api\validate\business;

use think\Validate;

class UserValid extends Validate
{

    protected $rule = [
        'account|登陆账号' => 'require|max:30|min:4',
        'pwd|登陆密码' => 'require|min:6|max:30'
    ];

    protected $scene = [
        'login' => ['account', 'pwd'],  //登录
    ];

}