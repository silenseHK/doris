<?php

namespace app\store\model;

use app\common\model\Goods as GoodsModel;
use app\common\model\GoodsStockLog;
use think\Db;
use think\db\Query;
use think\Exception;

/**
 * 商品模型
 * Class Goods
 * @package app\store\model
 */
class Goods extends GoodsModel
{

    public function add(array $data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = isset($data['content']) ? htmlspecialchars($data['content']) : '';
        $data['wxapp_id'] = $data['sku']['wxapp_id'] = self::$wxapp_id;
        $data['start_sale_time'] = strtotime($data['start_sale_time']);
        $data['end_sale_time'] = strtotime($data['end_sale_time']);

        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            ##处理总库存
            $total_stock = 0;
            if($data['spec_type'] == 20){ //多规格
                foreach($data['spec_many']['spec_list'] as $v){
                    $total_stock += $v['form']['stock_num'];
                }
            }else{
                $total_stock = $data['sku']['stock_num'];
            }
            $data['stock'] = $total_stock;
            $this->allowField(true)->save($data);
            $goods_id = $this->getLastInsID();

            // 商品规格
            $res = $this->addGoodsSpec($data);
            if(!is_bool($res) || $res !== true)throw new Exception($res);
            // 商品图片
            $this->addGoodsImages($data['images']);
            // 销售类型为层级代理的商品设置会员价格和返利
            if(isset($data['sale_type']) && $data['sale_type'] == 1){
                $res = $this->addGradeGoodsInfo(input('post.grade_goods/a'), $goods_id);
                if($res !== true)throw new Exception($res);
                ##获取游客价
                $tourist_price = GoodsGrade::getTouristPrice($goods_id);
                GoodsSku::updateGoodsSpecPrice($goods_id, $tourist_price);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加商品
     * @param array $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function add2(array $data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = isset($data['content']) ? htmlspecialchars($data['content']) : '';
        $data['wxapp_id'] = $data['sku']['wxapp_id'] = self::$wxapp_id;

        // 开启事务
        $this->startTrans();
        try {
            // 添加商品
            ##处理总库存
            $total_stock = 0;
            if($data['spec_type'] == 20){ //多规格
                if($data['sale_type'] == 2){//平台自营
                    foreach($data['spec_many']['spec_list'] as $v){
                        $total_stock += $v['form']['stock_num'];
                    }
                }else{ //多级代理
                    $total_stock = $data['sku2']['stock_num'];
                }
            }else{
                $total_stock = $data['sku']['stock_num'];
            }
            $data['stock'] = $total_stock;
            $this->allowField(true)->save($data);
            $goods_id = $this->getLastInsID();

            // 商品规格
            $res = $this->addGoodsSpec($data);
            if(!is_bool($res) || $res !== true)throw new Exception($res);
            // 商品图片
            $this->addGoodsImages($data['images']);
            // 销售类型为层级代理的商品设置会员价格和返利
            if(isset($data['sale_type']) && $data['sale_type'] == 1){
                $res = $this->addGradeGoodsInfo(input('post.grade_goods/a'), $goods_id);
                if($res !== true)throw new Exception($res);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加商品图片
     * @param $images
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function addGoodsImages($images)
    {
        $this->image()->delete();
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 层级代理商品设置会员价格和返利
     * @param $grades
     * @param $goods_id
     * @param $is_update
     * @return bool|string
     * @throws \Exception
     */
    private function addGradeGoodsInfo($grades, $goods_id, $is_update=false)
    {
        if(empty($grades))return '请设置会员价格';
        ## 增加会员等级商品信息
        $goodsGradeModel = new GoodsGrade();
        return $goodsGradeModel->addGoodsGradeInfo($grades, $goods_id, $is_update);
    }

    /**
     * 编辑商品
     * @param $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = isset($data['content']) ? htmlspecialchars($data['content']) : '';
        $data['wxapp_id'] = $data['sku']['wxapp_id'] = self::$wxapp_id;
        $data['start_sale_time'] = strtotime($data['start_sale_time']);
        $data['end_sale_time'] = strtotime($data['end_sale_time']);

        // 开启事务
        $this->startTrans();
        try {
            // 保存商品
            ##处理总库存
            $total_stock = 0;
            if($data['spec_type'] == 20){ //多规格
                foreach($data['spec_many']['spec_list'] as $v){
                    $total_stock += $v['form']['stock_num'];
                }
            }else{
                $total_stock = $data['sku']['stock_num'];
            }
            $data['stock'] = $total_stock;

            $this->allowField(true)->save($data);
            // 商品规格
            $res = $this->addGoodsSpec($data, true);
            if(!is_bool($res) || $res !== true)throw new Exception($res);
            // 商品图片
            $this->addGoodsImages($data['images']);
            // 销售类型为层级代理的商品设置会员价格和返利
            if(isset($data['sale_type']) && $data['sale_type'] == 1){
                $res = $this->addGradeGoodsInfo(input('post.grade_goods/a'), $this['goods_id'], true);
                if($res !== true)throw new Exception($res);
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加商品规格
     * @param $data
     * @param $isUpdate
     * @throws \Exception
     */
    private function addGoodsSpec(&$data, $isUpdate = false)
    {
        try{
            // 更新模式: 先删除所有规格
            $model = new GoodsSku;
            $isUpdate && $model->removeAll($this['goods_id']);  ##删除goods_spec_rel
            // 添加规格数据
            if ($data['spec_type'] == '10') {
                // 单规格
                $data['sku']['image_id'] = $data['images'][0];
                if($isUpdate){
                    $model->save($data['sku'],['goods_id'=>$this['goods_id']]);
                }else{
                    $data['sku']['total_stock_num'] = $data['sku']['stock_num'];
                    $data['sku']['goods_id'] = $this['goods_id'];
                    $model->isUpdate(false)->save($data['sku']);
                    $goods_sku_id = $model->getLastInsID();
                    GoodsStockLog::addLog($goods_sku_id,10,10, $data['sku']['stock_num']);
                }
            } else if ($data['spec_type'] == '20'){
//                if($data['sale_type'] == 2){
                    // 添加商品与规格关系记录
                    $model->addGoodsSpecRel($this['goods_id'], $data['spec_many']['spec_attr']);
                    // 添加商品sku
                    $model->addSkuList($this['goods_id'], $data['spec_many']['spec_list']);
//                }else{
//                    $specs = input('post.specs/a');
//                    if(!isset($specs['spec_val_id']) || empty($specs['spec_val_id']))throw new Exception('请设置商品规格');
//                    // 添加商品与规格关系记录
//                    $model->addAgentGoodsSpecRel($this['goods_id'], $specs);
//                    // 添加商品sku
//                    $model->addAgentSkuList($this['goods_id'], $data, $specs);
//                }
            }
            return true;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 修改商品状态
     * @param $state
     * @return false|int
     */
    public function setStatus($state)
    {
        return $this->allowField(true)->save(['goods_status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->allowField(true)->save(['is_delete' => 1]);
    }

    /**
     * 获取当前商品总数
     * @param array $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getGoodsTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }

    /**
     * 获取多级代理商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentGoodsList(){
        return Db::name('goods')->where([
                'sale_type' => 1,
                'is_delete' => 0,
                'stock' => ['GT', 0]
            ])
            ->field(['goods_id', 'goods_name'])
            ->order('goods_id','asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取代理商品的库存
     * @param $goodsSkuId
     * @return mixed
     */
    public static function getAgentGoodsStock($goodsSkuId){
        return Db::name('goods_sku')->where(['goods_sku_id'=>$goodsSkuId])->value('stock_num');
    }

    /**
     * 减少代理商品的库存
     * @param $goodsSkuId
     * @param $num
     * @return int|true
     * @throws Exception
     */
    public static function decAgentGoodsStock($goodsSkuId, $num){
        return Db::name('goods_sku')->where(['goods_sku_id'=>$goodsSkuId])->setDec('stock_num', $num);
    }

    /**
     * 增加代理商品的库存
     * @param $goodsSkuId
     * @param $num
     * @return int|true
     * @throws Exception
     */
    public static function incAgentGoodsStock($goodsSkuId, $num){
        return Db::name('goods_sku')->where(['goods_sku_id'=>$goodsSkuId])->setInc('stock_num', $num);
    }

    /**
     * 获取代理商品的积分信息
     * @param $goodsId
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentGoodsInfo($goodsId){
        return self::where(['goods_id'=>$goodsId])->field(['is_add_integral', 'integral_weight'])->find();
    }

    /**
     * 返还库存
     * @param $goods
     * @throws Exception
     */
    public static function refund($goods){
        self::where(['goods_id'=>$goods['goods_id']])->setInc('stock', $goods['total_num']);
        GoodsSku::where(['goods_sku_id'=>$goods['goods_sku_id']])->setInc('stock_num',$goods['total_num']);
    }

    /**
     * 代理商品列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentGoodsSkuList(){
        $list = $this
            ->where(['sale_type' => 1, 'is_delete'=>0])
            ->with(
                [
                    'sku' => function(Query $query){
                        $query->field(['goods_sku_id', 'goods_id', 'spec_sku_id']);
                    }
                ]
            )
            ->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual'])
            ->select();

        $data = [];
        foreach($list as $item){
            $it = [];
            $it['value'] = $item['goods_id'];
            $it['label'] = $item['goods_name'];
            $it['children'] = [];
            foreach($item['sku'] as $v){
                if(!$v['sku_list']){
                    $sku = '单规格';
                }else{
                    $sku = $v['sku_list'][0]['spec_name'] . ':' . $v['sku_list'][0]['spec_value'];
                }
                $it['children'][] = [
                    'value' => $v['goods_sku_id'],
                    'label' => $sku
                ];
            }
            $data[] = $it;
        }
        return $data;
    }

}
