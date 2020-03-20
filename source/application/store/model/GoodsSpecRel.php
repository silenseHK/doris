<?php

namespace app\store\model;

use app\common\model\GoodsSpecRel as GoodsSpecRelModel;
use think\Db;

/**
 * 商品规格关系模型
 * Class GoodsSpecRel
 * @package app\store\model
 */
class GoodsSpecRel extends GoodsSpecRelModel
{

    /**
     * 获取多级代理的规格信息
     * @param $goods_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentSpecData($goods_id){
//        var spec_id = 0;
//        var spec_val_id = [];
//        var spec_key = "";
//        var spec_val = [];
        $specs = (new self())->alias('gsr')
            ->join('spec_value sv','gsr.spec_value_id = sv.spec_value_id', 'LEFT')
            ->where([
                'gsr.goods_id' => $goods_id
            ])
            ->field(['gsr.spec_id', 'gsr.spec_value_id', 'sv.spec_value'])
            ->select()
            ->toArray();
        $spec_info['spec_id'] = $specs[0]['spec_id'];
        $spec_info['spec_val_id'] = array_column($specs, 'spec_value_id');
        $spec_info['spec_key'] = Db::name('spec')->where(['spec_id'=>$spec_info['spec_id']])->value('spec_name');
        $spec_info['spec_val'] = array_column($specs, 'spec_value');
        return $spec_info;
    }

}
