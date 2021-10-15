<?php


namespace app\common\model;


class GoodsSuggestion extends BaseModel
{

    protected $name = 'goods_suggestion';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    /**
     * 规格信息
     * @return \think\model\relation\BelongsTo
     */
    public function spec(){
        return $this->belongsTo('app\common\model\GoodsSku','goods_sku_id','goods_sku_id');
    }

    /**
     * 商品信息
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

    /**
     * 展示图
     * @return \think\model\relation\HasOne
     */
    public function image(){
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

}