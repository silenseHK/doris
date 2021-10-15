<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class ExchangeStockLog extends BaseModel
{

    protected $name = 'user_exchange_stock_log';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    /**
     * 关联老邀请人
     * @return \think\model\relation\BelongsTo
     */
    public function receiveUser(){
        return $this->belongsTo('app\common\model\User','receive_user_id','user_id');
    }

    /**
     * 一对多 --获取商品规格
     * @return \think\model\relation\BelongsTo
     */
    public function spec(){
        return $this->belongsTo('app\common\model\GoodsSku','goods_sku_id','goods_sku_id');
    }

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

}