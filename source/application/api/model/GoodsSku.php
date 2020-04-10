<?php

namespace app\api\model;

use app\common\model\GoodsSku as GoodsSkuModel;

/**
 * 商品规格模型
 * Class GoodsSku
 * @package app\api\model
 */
class GoodsSku extends GoodsSkuModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取规格信息
     * @param $spec_sku_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSpec($spec_sku_id){
        $spec_sku = explode('_',trim($spec_sku_id,'_'));
        $specs = [];
        foreach($spec_sku as $spec){
            $spec_data = SpecValue::where(['spec_value_id'=>$spec])->field(['spec_value_id', 'spec_value', 'spec_id'])->find();
            $specs[$spec_data['spec_id']] = $spec_data['spec_value_id'];
        }
        return $specs;
    }

    /**
     * 获取默认规格信息
     * @param $goods_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDefaultSpec($goods_id){
        $spec_sku_id = self::where(['goods_id'=>$goods_id])->value('spec_sku_id');
        return self::getSpec($spec_sku_id);
    }

}
