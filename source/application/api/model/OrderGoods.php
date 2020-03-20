<?php

namespace app\api\model;

use app\common\model\OrderGoods as OrderGoodsModel;

/**
 * 订单商品模型
 * Class OrderGoods
 * @package app\api\model
 */
class OrderGoods extends OrderGoodsModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'content',
        'wxapp_id',
        'create_time',
    ];

    /**
     * 获取未评价的商品
     * @param $order_id
     * @return OrderGoods[]|false
     * @throws \think\exception\DbException
     */
    public static function getNotCommentGoodsList($order_id)
    {
        return self::all(['order_id' => $order_id, 'is_comment' => 0], ['orderM', 'image']);
    }

    /**
     * 更新订单商品价格
     * @param $orderGoodsId
     * @param $price
     * @param $num
     * @return false|int
     */
    public static function editPrice($orderGoodsId, $price, $num){
        $totalPrice = $price * $num;
        return (new self)->save(['goods_price' => $price, 'total_price'=>$totalPrice, 'total_pay_price'=>$totalPrice, 'grade_goods_price'=>$price], ['order_goods_id'=>$orderGoodsId]);
    }

}
