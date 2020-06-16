<?php


namespace app\common\model;


class OrderRefundLog extends BaseModel
{

    protected $name = 'order_refund_log';

    protected $pk = 'refund_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id', 'admin_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

    public function setAdminIdAttr(){
        $admin = session('yoshop_store.user');
        return $admin['store_user_id'];
    }

}