<?php


namespace app\task\behavior\user;


use app\api\model\Setting as SettingModel;
use app\common\model\UserGoodsStock;
use app\common\service\order\Complete as OrderCompleteService;
use app\task\model\Order;
use app\task\model\User;
use app\api\model\User as UserApiMode;
use think\Cache;
use think\Db;
use think\Exception;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\task\model\user\BalanceLog as BalanceLogModel;

class CompleteOrder
{

    /**
     * 消费者订单物流订单超时未收货自动确认收货
     * @param $wxapp_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function run($wxapp_id){
        $wxapp_id = $wxapp_id?:10001;
        $cacheKey = "__task_space__[user/completeOrder]__{$wxapp_id}";
        if (!Cache::has($cacheKey)) {
            Cache::set($cacheKey, time(), 60 * 30);
            // 设置用户的会员等级
            $this->completeOrder();
        }
    }

    /**
     * 执行自动确认收货操作
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    ##执行自动完成订单任务[1.更新订单(状态、收货状态、收货时间) 2.减少出货人冻结库存 3.增加出货人余额]
    public function completeOrder(){
        $model = new Order();
        ##获取需自动完成订单列表
        $list = Order::getAutoCompleteList();
        if($list->isEmpty()){
            return false;
        }
        $order_ids = array_column($list->toArray(), 'order_id');
        Db::startTrans();
        try{
            foreach($list as $item){
//                if($item['supply_user_id']){
//                    $user_id = $item['supply_user_id'];
//                    ##减少冻结库存
//                    foreach($item['goods'] as $v){
//                        log_write('来了');
//                        $res = UserGoodsStock::disFreezeStockByUserGoodsId($user_id, $v['goods_sku_id'], $v['total_num']);
//                        if($res === false)throw new Exception('任务执行失败1');
//                    }
//
//                    ##增加余额
//                    $money = $item['pay_price'] - $item['express_price'];
//                    User::update(['balance'=>$money],['user_id'=>$user_id]);
//                    BalanceLogModel::add(SceneEnum::SALE, [
//                        'user_id' => $user_id,
//                        'money' => $money,
//                        'order_id' => $item['order_id'],
//                    ], ['order_no' => $item['order_no']]);

                    ## 增加积分、返利、出货人余额
                    $completed = Order::detail($item['order_id'], [
                        'user', 'address', 'goods', 'express',
                    ]);
                    UserApiMode::doIntegralRebate($completed);

                    if (SettingModel::getItem('trade')['order']['refund_days'] == 0) {
                        (new OrderCompleteService)->settled([$completed]);
                    }
//                }
            }
            ##修改订单
            $res = $model->save(
                [
                    'receipt_status' => 20,
                    'receipt_time' => time(),
                    'receipt_type' => 20,
                    'order_status' => 30
                ],
                [
                    'order_id' => ['in', $order_ids]
                ]
            );

            if($res === false)throw new Exception('任务执行失败2');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            log_write($e->getMessage());
            return $e->getMessage();
        }
    }

}