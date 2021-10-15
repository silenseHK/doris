<?php


namespace app\common\model\delivery;


use app\common\model\BaseModel;

class OrderRequest extends BaseModel
{

    protected $name = 'delivery_order_request';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

}