<?php


namespace app\task\behavior\user;


use app\task\model\user\OrderDeliver;
use app\store\model\UserGoodsStock;
use think\Cache;
use think\Db;
use think\Exception;

class CancelDeliverOrder
{


    /**
     * ##提货发货订单-自提订单3天未确认收货自动取消
     * @param $wxapp_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run($wxapp_id){
        $cacheKey = "__task_space__[user/CancelDeliverOrder]__{$wxapp_id}";
        if (!Cache::has($cacheKey)) {
            // 设置用户的会员等级
            $this->cancelDeliverOrder();
            Cache::set($cacheKey, time(), 60 * 10);
        }
    }

    /**
     * 执行定时取消自提订单任务
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelDeliverOrder(){
        $model = new OrderDeliver();
        ##获取需要自动取消的列表
        $list = OrderDeliver::getAutoCancelList();
        if ($list->isEmpty()) {
            return false;
        }
        ##取消订单[1.修改订单状态；2.返还库存；3.减少冻结库存；4.增加库存变更记录]
        $deliver_ids = array_column($list->toArray(), 'deliver_id');
        Db::startTrans();
        try{
            foreach($list as $v){
                ##返还库存
                $res = UserGoodsStock::backStock($v,'提货发货超时自动取消发货');
                if($res !== true)throw new Exception($res);
            }
            ##修改订单状态为取消
            $res = $model->where(['deliver_id'=>['in', $deliver_ids]])->setField('deliver_status', 30);

            if($res === false)throw new Exception('任务执行失败了');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

}