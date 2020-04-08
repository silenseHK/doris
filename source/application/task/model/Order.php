<?php

namespace app\task\model;

use app\common\model\Order as OrderModel;
use think\db\Query;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends OrderModel
{
    /**
     * 获取订单列表
     * @param array $filter
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($filter = [], $with = [])
    {
        return $this->with($with)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->select();
    }

    /**
     * 获取超时需自动完成订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAutoCompleteList(){
        $model = new self;
        $model->setAutoCompleteWhere();
        return $model->field(['order_id', 'order_no', 'pay_price', 'express_price', 'supply_user_id', 'order_status', 'pay_status', 'delivery_status', 'receipt_status'])->with(['goods'=>function(Query $query){$query->field(['order_id', 'total_num', 'goods_id', 'goods_sku_id']);}])->select();
    }

    /**
     * 设置超时需自动完成筛选条件
     */
    public function setAutoCompleteWhere(){
        $limit_time = time() - 7 * 24 * 60 * 60;
        $this->where(
            [
                'pay_status' => 20, #已支付
                'delivery_type' => 10, #物流发货
                'delivery_status' => 20, #待收货
                'delivery_time' => ['LT', $limit_time], #待收货时间超时
                'receipt_status' => 10, #未收货
                'order_status' => 10 #订单状态正在进行中
            ]
        );
    }

}
