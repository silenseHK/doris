<?php


namespace app\common\validate;


use think\Validate;

class TransferValid extends Validate
{

    protected $rule = [
        'openid' => 'require',
        'stock' => 'require'
    ];

    protected $scene = [
        'file_transfer_stock' => ['openid', 'stock']
    ];

}