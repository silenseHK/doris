<?php


namespace app\api\model\user\deliver;

use app\api\model\user\GoodsStock;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\model\store\shop\Order as ShopOrder;
use app\common\model\user\OrderDeliver as OrderDeliverModel;
use think\Db;
use think\Exception;

class OrderDeliver extends OrderDeliverModel
{

    protected $error = '';

    /**
     * 详情
     * @param $where
     * @param array $with
     * @return OrderDeliver|null
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with=[
        'goods',
        'user',
        'extract',
        'express',
        'spec.image'
    ])
    {
        is_array($where) ? $filter = $where : $filter['deliver_id'] = (int)$where;
        $model = self::get($filter, $with);
        $model['order_id'] = $model['deliver_id'];
        return $model;
    }

    /**
     * 确认核销（自提订单）
     * @param int $extractClerkId 核销员id
     * @return bool|false|int
     */
    public function verificationOrder($extractClerkId)
    {
        if($this['pay_status']['value'] != 20 || $this['deliver_status']['value'] != 20 || $this['deliver_type']['value'] != 20){
            $this->error = '该订单不支持此操作';
            return false;
        }
        Db::startTrans();
        try{
            ##执行确认收货操作
            $data = [
                'complete_type' => 30,
                'complete_time' => time(),
                'deliver_status' => 40,
                'extract_clerk_id' => $extractClerkId
            ];
            $res = $this->isUpdate(true)->save($data);
            if($res === false)throw new Exception('操作失败');
            ##减少冻结的库存
            if(GoodsStock::disFreezeStockByUserGoodsId($this['user_id'], $this['goods_sku_id'], $this['goods_num'],1) === false)throw new Exception('操作失败');

            // 新增订单核销记录
            ShopOrder::add(
                $this['deliver_id'],
                $this['extract_shop_id'],
                $this['extract_clerk_id'],
                OrderTypeEnum::SELF_DELIVERY
            );
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getError(){
        return $this->error;
    }

}