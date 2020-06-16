<?php


namespace app\store\validate;


use think\Validate;

class UserValid extends Validate
{

    protected $rule = [
        'user_id' => 'require|number|>=:1',
        'mobile' => 'require|mobile',
        'exchange_user_id' => 'require|number|>=:0',
        'goods_id' => 'require|number|>=:1',
        'num' => 'require|number|>=:1',
        'is_platform_rebate' => 'require|number|in:0,1',
        'remark' => 'require|max:200'
    ];

    protected $scene = [
        'exchange_team' => ['user_id', 'exchange_user_id'],  ##转换团队
        'rebate' => ['user_id', 'goods_id', 'num', 'remark']
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