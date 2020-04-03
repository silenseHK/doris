<?php

namespace app\store\model;

use app\common\model\GoodsSku as GoodsSkuModel;

/**
 * 商品规格模型
 * Class GoodsSku
 * @package app\store\model
 */
class GoodsSku extends GoodsSkuModel
{
    /**
     * 批量添加商品sku记录
     * @param $goods_id
     * @param $spec_list
     * @return array|false
     * @throws \Exception
     */
    public function addSkuList($goods_id, $spec_list)
    {
        $ids = [];
        foreach ($spec_list as $item) {
            $form = $item['form'];
            if(isset($form['goods_sku_id'])){ ##修改
                $data = array_merge($form, [
                    'spec_sku_id' => $item['spec_sku_id'],
                ]);
                unset($data['goods_sku_id']);
                $id = $form['goods_sku_id'];
                $this->allowField(true)->isUpdate(true)->save($data, ['goods_sku_id'=>$id]);
                $ids[] = $id;
            }else{ ##新增
                $data = array_merge($form, [
                    'spec_sku_id' => $item['spec_sku_id'],
                    'goods_id' => $goods_id,
                    'wxapp_id' => self::$wxapp_id,
                ]);
                $this->allowField(true)->isUpdate(false)->save($data);
                $ids[] = $this->getLastInsID();
            }
        }
        ##删除
        $this->where(['goods_id'=>$goods_id, 'goods_sku_id'=>['NOT IN', $ids]])->delete();
        return true;
    }

    /**
     * 批量添加多级代理商品sku记录
     * @param $goods_id
     * @param $data
     * @param $specs
     * @return array|false
     * @throws \Exception
     */
    public function addAgentSkuList($goods_id, $data, $specs){
        $add_data = [];
        foreach($specs['spec_val_id'] as $v){
            $data2['spec_sku_id'] = $v;
            $data2['goods_id'] = $goods_id;
            $data2['goods_no'] = $data['sku2']['goods_no'];
            $data2['goods_price'] = $data['sku2']['goods_price'];
            $data2['line_price'] = $data['sku2']['line_price'];
            $data2['stock_num'] = 0;
            $data2['goods_weight'] = $data['sku2']['goods_weight'];
            $data2['wxapp_id'] = self::$wxapp_id;
            $add_data[] = $data2;
        }
        return $this->allowField(true)->saveAll($add_data);
    }

    /**
     * 添加商品规格关系记录
     * @param $goods_id
     * @param $spec_attr
     * @return array|false
     * @throws \Exception
     */
    public function addGoodsSpecRel($goods_id, $spec_attr)
    {
        $data = [];
        array_map(function ($val) use (&$data, $goods_id) {
            array_map(function ($item) use (&$val, &$data, $goods_id) {
                $data[] = [
                    'goods_id' => $goods_id,
                    'spec_id' => $val['group_id'],
                    'spec_value_id' => $item['item_id'],
                    'wxapp_id' => self::$wxapp_id,
                ];
            }, $val['spec_items']);
        }, $spec_attr);
        $model = new GoodsSpecRel;
        return $model->saveAll($data);
    }

    /**
     * 添加多级代理商品规格关系记录
     * @param $goods_id
     * @param $specs
     * @return array|false
     * @throws \Exception
     */
    public function addAgentGoodsSpecRel($goods_id, $specs){
        $data = [];
        foreach($specs['spec_val_id'] as $v){
            $data[] = [
                'spec_value_id' => (int)$v,
                'spec_id' => (int)$specs['spec_id'],
                'goods_id' => $goods_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }
        $model = new GoodsSpecRel();
        return $model->saveAll($data);
    }

    /**
     * 移除指定商品的所有sku
     * @param $goods_id
     * @return int
     */
    public function removeAll($goods_id)
    {
        $model = new GoodsSpecRel;
        return $model->where('goods_id','=', $goods_id)->delete();
//        return $this->where('goods_id','=', $goods_id)->delete();
    }

    /**
     * 更新商品规格价格
     * @param $goods_id
     * @param $price
     * @return GoodsSku
     */
    public static function updateGoodsSpecPrice($goods_id, $price){
        return self::update(['goods_price'=>$price], ['goods_id'=>$goods_id]);
    }

}
