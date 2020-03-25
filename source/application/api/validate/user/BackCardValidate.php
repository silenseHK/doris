<?php


namespace app\api\validate\user;


use think\Validate;

class BackCardValidate extends Validate
{

    protected $rule = [
        'page|页码' => 'number|>=:1',
        'size|每页条数' => 'number|>=:1',
        'card_account' => 'require',
        'card_number|银行卡号' => 'require|number',
        'bank_address|开户行地址' => 'require',
        'bank_id|开户行' => 'require|number|>=:1',
        'is_default|设置默认银行卡' => 'in:0,1',
        'card_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'lists' => ['page', 'size'],
        'add' => ['card_number', 'bank_address', 'bank_id', 'is_default', 'card_account'],
        'edit' => ['card_id', 'card_number', 'bank_address', 'bank_id', 'is_default', 'card_account'],
        'del' => ['card_id']
    ];

}