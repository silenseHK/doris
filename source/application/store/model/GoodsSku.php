<?php

namespace app\store\model;

use app\common\model\GoodsSku as GoodsSkuModel;
use app\common\model\GoodsStockLog;
use think\db\Query;
use think\Exception;

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
                    'total_stock_num' => $form['stock_num']
                ]);
                unset($data['image_path']);
                $goods_sku_id = $this->insertGetId($data);
                $ids[] = $goods_sku_id;
                GoodsStockLog::addLog($goods_sku_id,10,10, $form['stock_num']);
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

    /**
     * 商品规格信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsSpec(){
        $goods_id = input('post.goods_id',0,'intval');
        ##获取规格
        $list = $this->where(['goods_id'=>$goods_id])->field(['goods_sku_id', 'spec_sku_id'])->select();
        $spec_id = 0;
        foreach($list as &$item){
            if($item['sku_list']){
                $attr = "";
                foreach($item['sku_list'] as $vi){
                    $attr .= "{$vi['spec_name']}:{$vi['spec_value']},";
                }
                $item['attr'] = trim($attr,',');
            }else{
                $spec_id = $item['goods_sku_id'];
            }
        }
        return compact('list','spec_id');
    }

    public static function getGoodsSpecList(){
//        return (new self)->with(['goods','image'])->select();
        return (new self)->alias('gs')
            ->join('goods g','g.goods_id = gs.goods_id','LEFT')
            ->where(
                [
                    'g.is_delete' => 0
                ]
            )
            ->with(
                [
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    }
                ]
            )
            ->field("gs.total_stock_num,gs.stock_num,gs.spec_sku_id,gs.image_id,g.is_delete,g.goods_name")
            ->select();
    }

    /**
     * 规格商品的库存
     * @param int $goods_sku_id
     * @return mixed
     */
    public function getSkuStock($goods_sku_id=0){
        if(!$goods_sku_id)
            $goods_sku_id = input('post.goods_sku_id',0,'intval');
        return $this->where(['goods_sku_id'=>$goods_sku_id])->value('stock_num');
    }

    /**
     * 补充库存
     * @return bool
     * @throws Exception
     */
    public function recharge(){
        $goods_sku_id = input('post.goods_sku_id',0,'intval');
        $mode = input('post.mode','','str_filter');
        $num = input('post.num',0,'intval');
        if(!$goods_sku_id || !$mode || !$num || $num<=0){
            throw new Exception('参数错误');
        }

        $goods_sku = self::get($goods_sku_id);

        if($mode == 'dec'){ ##判断库存是否充足
            $balance_stock = $this->getSkuStock($goods_sku_id);
            if($balance_stock < $num)throw new Exception('当前库存不足');
        }
        $update = [
            'stock_num' => [$mode, $num],
            'total_stock_num' => [$mode, $num]
        ];

        $change_direction = $mode=='inc'?10:20;
        GoodsStockLog::addLog($goods_sku_id,20,$change_direction,$num);

        $res = $this->update($update, ['goods_sku_id'=>$goods_sku_id]);
        if($res === false)throw new Exception('操作失败');

        ##增加商品总库存
        Goods::update(['stock'=>[$mode, $num]], ['goods_id'=>$goods_sku['goods_id']]);
        return true;
    }

}
