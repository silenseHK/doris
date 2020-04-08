<?php

namespace app\common\model;

use app\common\enum\user\StockChangeScene;
use app\common\model\GoodsSku as GoodsSkuModel;
use app\common\enum\goods\DeductStockType as DeductStockTypeEnum;
use app\common\model\Order as OrderModel;
use think\Db;
use think\Exception;
use think\Log;

/**
 * 订单商品模型
 * Class OrderGoods
 * @package app\common\model
 */
class OrderGoods extends BaseModel
{
    protected $name = 'order_goods';
    protected $updateTime = false;

    /**
     * 订单商品列表
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        $model = "app\\common\\model\\UploadFile";
        return $this->belongsTo($model, 'image_id', 'file_id');
    }

    /**
     * 关联商品表
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('Goods');
    }

    /**
     * 关联商品sku表
     * @return \think\model\relation\BelongsTo
     */
    public function sku()
    {
        return $this->belongsTo('GoodsSku', 'spec_sku_id', 'spec_sku_id');
    }

    /**
     * 关联商品规格
     * @return \think\model\relation\BelongsTo
     */
    public function spec(){
        return $this->belongsTo('app\common\model\GoodsSku','goods_sku_id','goods_sku_id');
    }

    /**
     * 关联订单主表
     * @return \think\model\relation\BelongsTo
     */
    public function orderM()
    {
        return $this->belongsTo('Order');
    }

    /**
     * 售后单记录表
     * @return \think\model\relation\HasOne
     */
    public function refund()
    {
        return $this->hasOne('OrderRefund');
    }

    /**
     * 订单商品详情
     * @param $where
     * @return OrderGoods|null
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return static::get($where, ['image', 'refund']);
    }

    /**
     * 回退商品库存
     * @param $goodsList
     * @param $isPayOrder  *是否支付
     * @return array|false
     * @throws \Exception
     */
    public function backGoodsStock2($goodsList, $isPayOrder = false)
    {
        $data = $agentData = [];

        foreach ($goodsList as $goods) {
            if($goods['sale_type'] == 1){ ##层级代理商品
                $item = [
                    'goods_id' => $goods['goods_id'],
                    'stock' => $goods['total_num'],
                    'order_id' => $goods['order_id'],
                    'integral_weight' => $goods['integral_weight']
                ];
                if ($isPayOrder == true){
                    $agentData[] = $item;
                }else{
                    $goods['deduct_stock_type'] == DeductStockTypeEnum::CREATE && $agentData[] = $item;
                }
            }else{ ##直营商品
                $item = [
                    'goods_sku_id' => $goods['goods_sku_id'],
                    'stock_num' => ['inc', $goods['total_num']]
                ];
                if ($isPayOrder == true) {
                    // 付款订单全部库存
                    $data[] = $item;
                } else {
                    // 未付款订单，判断必须为下单减库存时才回退
                    $goods['deduct_stock_type'] == DeductStockTypeEnum::CREATE && $data[] = $item;
                }
            }

        }

        if (!empty($data)){
            // 更新商品规格库存
            $model = new GoodsSkuModel;
            $res = $model->isUpdate()->saveAll($data);
        }

        if (!empty($agentData)){
            $stockLogData = $platformData = [];
            foreach($agentData as $v){
                $orderInfo = Order::where(['order_id'=>$v['order_id']])->field(['user_id', 'supply_user_id'])->find();
                $supplyUserId = $orderInfo['supply_user_id'];
                if($supplyUserId > 0){ ##出货人是用户
                    UserGoodsStock::incStockByUserGoodsId($supplyUserId, $v['goods_id'], $v['stock']);
                    ### 添加库存变更记录
                    $stockLogData[] = [
                        'user_id' => $supplyUserId,
                        'goods_id' => $v['goods_id'],
                        'balance_stock' => UserGoodsStock::getStock($supplyUserId, $v['goods_id']),
                        'change_num' => $v['stock'],
                        'opposite_user_id' => $orderInfo['user_id'],  //发货人id
                        'remark' => '用户退货',
                        'integral_weight' => $v['integral_weight'],
                        'integral_log_id' => 0
                    ];
                }else{ ## 出货人是平台
                    $platformData[] = [
                        'goods_id' => $v['goods_id'],
                        'stock' => ['inc', $v['stock']]
                     ];
                }
            }
            if(!empty($stockLogData)){
                $res = UserGoodsStockLog::insertAllData($stockLogData);
            }
            if(!empty($platformData)){
                $model = new Goods;
                $res = $model->isUpdate()->saveAll($platformData);
            }
        }

        return isset($res) ? $res : true;
    }

    /**
     * 返还库存
     * @param $order
     * @param bool $isPayOrder
     * @return bool|string
     */
    public function backGoodsStock($order, $isPayOrder = false){
        ##未支付不返库存
        if(!$isPayOrder)return true;
        $goodsList = $order['goods'];
        $stockData = [];
        foreach($goodsList as $goods){
            if(!isset($stockData[$goods['goods_sku_id']])){
                $stockData[$goods['goods_sku_id']] = [
                    'stock' => 0,
                    'goods_id' => $goods['goods_id']
                ];
            }
            $stockData[$goods['goods_sku_id']]['stock'] += $goods['total_num'];
        }
        Db::startTrans();
        try{
            if($order['supply_user_id'] > 0){ ##代理人出货
                $supply_user_id = $order['supply_user_id'];
                foreach($stockData as $key => $item){
                    ##增加库存变化记录
                    $data = [
                        'user_id' => $supply_user_id,
                        'goods_id' => $item['goods_id'],
                        'goods_sku_id' => $key,
                        'balance_stock' => UserGoodsStock::getStock($supply_user_id, $key),
                        'change_num' => $item['stock'],
                        'opposite_user_id' => 0,  //发货人id
                        'remark' => '用户取消订单返还库存',
                        'change_type' => StockChangeScene::SALE,  //出货取消返库存
                        'change_direction' => 10  //增加
                    ];
                    $res = UserGoodsStockLog::insertData($data);
                    if($res === false)throw new Exception('库存返还失败');
                    ##返回代理人库存 减少代理库存
                    $res = UserGoodsStock::disFreezeStockByUserGoodsId($supply_user_id, $key, $item['stock'],2);
                    if($res === false)throw new Exception('操作失败');
                }

            }else{ ##平台出货
                ##直接恢复库存
                foreach($stockData as $key => $item){
                    $res = GoodsSku::update(['stock_num'=>['inc', $item['stock']]], ['goods_sku_id'=>$key]);
                    if($res === false)throw new Exception('操作失败');
                }
            }

            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();;
            return $e->getMessage();
        }
    }

}
