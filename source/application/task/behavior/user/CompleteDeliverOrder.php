<?php


namespace app\task\behavior\user;


use app\common\model\PlatformIncomeLog;
use app\common\model\UserGoodsStock;
use app\task\model\user\OrderDeliver;
use think\Cache;
use think\Db;
use think\Exception;

class CompleteDeliverOrder
{

    /**
     * ##提货发货-物流订单超时自动确认收货
     * @param $wxapp_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run($wxapp_id){
        $wxapp_id = $wxapp_id?:10001;
        $cacheKey = "__task_space__[user/CompleteDeliverOrder]__{$wxapp_id}";
        if (!Cache::has($cacheKey)) {
            Cache::set($cacheKey, time(), 60 * 30);
            // 设置用户的会员等级
            $this->completeDeliverOrder();
        }
    }

    /**
     * 自动完成订单
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function completeDeliverOrder(){
        $model = new OrderDeliver();
        ##获取待自动完成订单列表
        $list = OrderDeliver::getAutoCompleteList();
        if ($list->isEmpty()) {
            return false;
        }
        ##确认收货[1.修改状态 2.减少冻结库存]
        $deliver_ids = array_column($list->toArray(), 'deliver_id');
        Db::startTrans();
        try{
            foreach($list as $v){
                ##减少冻结库存
                $res = UserGoodsStock::disFreezeStockByUserGoodsId($v['user_id'],$v['goods_sku_id'],$v['goods_num']);
                if($res === false)throw new Exception('任务执行失败');
                if($v['freight_money'] > 0){
                    PlatformIncomeLog::addLog([
                        'money' => $v['freight_money'],
                        'order_no' => $v['order_no'],
                        'type' => 20,
                        'direction' => 10,
                        'order_type' => 20
                    ]);
                }
            }
            ##修改订单状态为已完成
            $res = $model->save(['deliver_status'=>40, 'complete_time'=>time(), 'complete_type'=>10], ['deliver_id'=>['in', $deliver_ids]]);
            if($res === false)throw new Exception('任务执行失败');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            log_write($e->getMessage(),'error');
            return $e->getMessage();
        }

    }

}