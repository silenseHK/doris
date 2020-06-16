<?php


namespace app\common\model;


class GoodsExperience extends BaseModel
{

    protected $name = 'goods_experience';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

    /**
     * 关联-推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function firstUser(){
        return $this->belongsTo('app\common\model\User','first_user_id','user_id');
    }

    /**
     * 关联-二级推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function secondUser(){
        return $this->belongsTo('app\common\model\User','second_user_id','user_id');
    }

    /**
     * 关联-下单人
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    /**
     * 关联-订单数据
     * @return \think\model\relation\BelongsTo
     */
    public function orderData(){
        return $this->belongsTo('app\common\model\Order','order_id','order_id');
    }

}