<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class OrderDeliver extends BaseModel
{

    protected $name = "order_deliver";

    /**
     * 生成订单号
     * @param $deliverType *发货方式
     * @return string
     */
    public function makeOrderNo($deliverType){
        $orderNo = time() . rand(1000000, 9999999);
        return $deliverType == 10 ? '201' . $orderNo : '202' . $orderNo;
    }

    /**
     * 获取用户信息
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('\app\common\model\User','user_id','user_id');
    }

}