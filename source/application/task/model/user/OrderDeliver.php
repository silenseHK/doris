<?php


namespace app\task\model\user;

use \app\common\model\user\OrderDeliver as OrderDeliverModel;

class OrderDeliver extends OrderDeliverModel
{

    /**
     * 获取需要自动取消的自提订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAutoCancelList(){
        $model = new self;
        $model->setAutoCancelWhere();
        return $model->field(['deliver_id', 'goods_id', 'user_id', 'goods_num'])->select();
    }

    /**
     * 设置需要自动取消的筛选条件
     */
    public function setAutoCancelWhere(){
        $limit_time = time() - 3 * 24 * 60 * 60;
        ##设置条件
        $this->where(['deliver_type'=>20, 'pay_status'=>20, 'deliver_status'=>20, 'deliver_time'=> ['LT', $limit_time]]);
    }

    /**
     * 设置需要自动完成的物流订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAutoCompleteList(){
        $model = new self;
        $model->setAutoCompleteWhere();
        return $model->field(['deliver_id', 'goods_id', 'user_id', 'goods_num'])->select();
    }

    /**
     * 设置需要自动确认收货订单筛选条件
     */
    public function setAutoCompleteWhere(){
        $limit_time = time() - 7 * 24 * 60 * 60;
        ##设置条件
        $this->where(['deliver_type'=>10, 'pay_status'=>20, 'deliver_status'=>20, 'deliver_time'=> ['LT', $limit_time]]);
    }

}