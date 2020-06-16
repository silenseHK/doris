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

    /**
     * 检查体验装购买权限【体验装只能购买一份】
     * @param $goods_id
     * @param $user_id
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkExperienceOrder($goods_id, $user_id){
        $model = new self;
        $info = $model->alias('og')
            ->join('order o','o.order_id = og.order_id','LEFT')
            ->where([
                'og.user_id' => $user_id,
                'og.goods_id' => $goods_id,
                'o.order_status' => ['IN', [10, 30]]
            ])
            ->field('o.pay_status')
            ->order('o.create_time','desc')
            ->find();
        if(!$info)return true;
        if($info['pay_status'] == 10)return "体验装只能购买一份，请前往支付或取消您的未完成订单";
        if($info['pay_status'] == 20)return "体验装只能购买一份";
        return true;
    }

}
