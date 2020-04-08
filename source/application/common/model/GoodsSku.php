<?php

namespace app\common\model;

use think\Db;

/**
 * 商品SKU模型
 * Class GoodsSku
 * @package app\common\model
 */
class GoodsSku extends BaseModel
{
    protected $name = 'goods_sku';

    protected $append = ['sku_list'];

    /**
     * 规格图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

    /**
     * 规格列表
     * @param $value
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSkuListAttr($value, $data){
        if(!isset($data['spec_sku_id']) || !$data['spec_sku_id'])return [];
        $spec_arr = explode('_',$data['spec_sku_id']);
        return $this->getSpecList($spec_arr);
    }

    /**
     * 规格列表
     * @param $spec_arr
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSpecList($spec_arr){
        return Db::name('spec_value')->alias('sv')
            ->join('spec s','s.spec_id = sv.spec_id','LEFT')
            ->where(['sv.spec_value_id'=>['IN',$spec_arr]])
            ->field('s.spec_name,sv.spec_value')
            ->select()
            ->toArray();
    }

}
