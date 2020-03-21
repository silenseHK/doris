<?php


namespace app\api\validate\user;


use think\Validate;

class BackCardValidate extends Validate
{

    protected $rule = [
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1',
        'card_number|银行卡号' => 'require|number',
        'bank_address|开户行地址' => 'require',
        'bank_name|开户行名字' => 'require',
        'is_default|设置默认银行卡' => 'in:0,1'
    ];

    protected $scene = [
        'lists' => ['page', 'size'],
        'add' => ['card_number', 'bank_address', 'bank_name', 'is_default'],
        'edit' => ['card_id', 'card_number', 'bank_address', 'bank_name', 'is_default']
    ];

}