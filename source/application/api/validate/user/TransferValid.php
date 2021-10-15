<?php


namespace app\api\validate\user;


use think\Validate;

class TransferValid extends Validate
{

    protected $rule = [
        'nickname' => 'require',
        'headimgurl' => 'require',
        'openid' => 'require',
        'phone' => 'require',
        'level' => 'require'
    ];

    protected $scene = [
        'transfer_agent' => ['nickname', 'headimgurl', 'openid', 'phone', 'level']
    ];

}