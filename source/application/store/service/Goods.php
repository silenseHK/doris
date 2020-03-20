<?php

namespace app\store\service;

use app\common\service\Goods as Bases;
use app\store\model\Category as CategoryModel;
use app\store\model\Delivery as DeliveryModel;
use app\store\model\GoodsGrade;
use app\store\model\GoodsSpecRel;
use app\store\model\user\Grade as GradeModel;
use app\store\model\Goods as GoodsModel;

/**
 * 商品服务类
 * Class Goods
 * @package app\store\service
 */
class Goods extends Bases
{
    /**
     * 商品管理公共数据
     * @param null $model
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getEditData($model = null)
    {
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        // 配送模板
        $delivery = DeliveryModel::getAll();
        // 会员等级列表
        $gradeList = GradeModel::getUsableList()->toArray();
        // 商品sku数据
        $specData = 'null';
        if (!is_null($model) && $model['spec_type'] == 20) {
            $specData = json_encode($model->getManySpecData($model['spec_rel'], $model['sku']));
        }
        // 层及代理的会员价格和返利
        $goodsGradeList = [];
        if (!is_null($model) && $model['sale_type'] == 1){
            $goodsGradeList = GoodsGrade::getGoodsGradeList($model['goods_id']);
            if($goodsGradeList){
                foreach($gradeList as $k =>$v){
                    $gradeList[$k]['price'] = $goodsGradeList[$k]['price'];
                    $gradeList[$k]['rebate'] = $goodsGradeList[$k]['rebate'];
                }
            }
        }
        ##修改商品时：商品的sale_type=>1 并且 spec_type=>20
        $specData2 = [];
        $spec_val = [
            'spec_id' => 0,
            'spec_val_id' => [],
            'spec_key' => 0,
            'spec_val' => []
        ];
        if(!is_null($model) && $model['sale_type'] == 1 && $model['spec_type'] == 20){
            ##商品价格
            $specData2 = json_decode($specData,true);
            ##规格数组
            $spec_val = GoodsSpecRel::getAgentSpecData($model['goods_id']);
            $specData = 'null';
        }

//        print_r(compact('catgory', 'delivery', 'gradeList', 'specData', 'goodsGradeList', 'specData2', 'spec_val'));die;

        return compact('catgory', 'delivery', 'gradeList', 'specData', 'goodsGradeList', 'specData2', 'spec_val');
    }


}