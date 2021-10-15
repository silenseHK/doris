<?php


namespace app\store\model;

use app\common\enum\user\StockChangeScene;
use app\common\model\UserGoodsStock as UserGoodsStockModel;
use app\store\model\user\ExchangeStockLog;
use think\Db;
use think\db\Query;
use think\Exception;
use app\common\model\UserGoodsStockLog;

class UserGoodsStock extends UserGoodsStockModel
{

    /**
     * 获取用户代理商品库存
     * @param int $user_id
     * @param int $goods_sku_id
     * @return int|string
     */
    public static function getUserGoodsStock($user_id=0, $goods_sku_id=0){
        try{
            ##接收参数
            if(!$user_id || !$goods_sku_id){
                $user_id = input('post.user_id', 0,'intval');
                $goods_sku_id = input('post.goods_sku_id', 0,'intval');
            }
            if($user_id <= 0 || $goods_sku_id < 0)throw new Exception('参数错误');

            $stock = self::where([
                    'user_id' => $user_id,
                    'goods_sku_id' => $goods_sku_id
                ])
                ->value('stock');
            return (int)$stock;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 检查用户代理商品数据是否存在
     * @param $user_id
     * @param $goods_sku_id
     * @return int|string
     * @throws Exception
     */
    public static function checkDataExist($user_id, $goods_sku_id){
        return (new self)->where([
                'user_id' => $user_id,
                'goods_sku_id' => $goods_sku_id
            ])
            ->value('id');
    }

    /**
     * 更新用户代理商品库存
     * @param $userId
     * @param $goodsId
     * @param $goodsSkuId
     * @param $finalStock
     * @param $diffStock
     * @param $changeType
     * @param $remark
     * @param int $oppositeUserId
     * @return bool|string
     */
    public static function updateUserGoodsStock($userId, $goodsId, $goodsSkuId, $finalStock, $diffStock, $changeType, $remark, $oppositeUserId=0){
        Db::startTrans();
        try{
            ##变更库存
            $userGoodsStockId = self::checkDataExist($userId, $goodsSkuId);
            if($userGoodsStockId){
                ##更新
                $res = self::where(['id' => $userGoodsStockId])->setField('stock', $finalStock);
            }else{
                ##新增
                $res = UserGoodsStock::insertUserGoodsStock($userId, $goodsId, $goodsSkuId, $finalStock);
            }
            if($res === false)throw new Exception('库存变更失败');
            ##增加变更记录
            $balanceStock = $finalStock - $diffStock;

            ##获取商品积分信息
            $goodsInfo = Goods::getAgentGoodsInfo($goodsId);
            $res = \app\store\model\UserGoodsStockLog::addLog([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'balance_stock' =>$balanceStock,
                'diff_stock' => $diffStock,
                'change_type' => $changeType,
                'remark' => $remark,
                'opposite_user_id' => $oppositeUserId,
                'integral_weight' => $goodsInfo['integral_weight'],
                'wxapp_id' => static::$wxapp_id
            ]);
            if($res === false)throw new Exception('库存变更日志添加失败');
            $stockLogId = (new \app\store\model\UserGoodsStockLog)->getLastInsID();

            ##增加用户会员积分&&更新会员等级
            if($diffStock > 0){
                $integralLogId = User::incUserIntegralByGoodsId($userId, $goodsId, $diffStock);
                if(is_string($integralLogId))throw new Exception($integralLogId);
                ##回填库存变更记录的积分变更表id
                if($integralLogId > 0)\app\store\model\UserGoodsStockLog::editIntegralLogId(['id'=>$stockLogId, 'integral_log_id'=>$integralLogId]);
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 新增用户代理商品库存数据
     * @param $userId
     * @param $goodsId
     * @param $goodsSkuId
     * @param $stock
     * @return false|int
     */
    protected static function insertUserGoodsStock($userId, $goodsId, $goodsSkuId, $stock){
        $data = [
            'user_id' => $userId,
            'goods_id' => $goodsId,
            'goods_sku_id' => $goodsSkuId,
            'stock' => $stock,
            'wxapp_id' => static::$wxapp_id
        ];
        return (new self)->save($data);
    }

    /**
     * 获取库存列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($params){
        $user_id = intval($params['user_id']);
        $list = $this->where(['user_id'=>$user_id])->with(
            [
                'goods' => function(Query $query){
                    $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                },
                'spec' => function(Query $query){
                    $query->field(['goods_sku_id', 'image_id', 'spec_sku_id'])->with(
                        [
                            'image'=>function(Query $query){
                                $query->field(['file_id', 'storage', 'file_name']);
                            }
                        ]
                    );
                }
            ]
        )->order('id','desc')->select();
        foreach($list as &$data){
            $specs = '';
            foreach($data['spec']['sku_list'] as $k => $v){
                $specs .= $v['spec_name'] .'：'. $v['spec_value'] . '，';
            }
            $data['specs'] = trim($specs, '，');
        }
        return $list;
    }

    /**
     * 返还提货发货库存
     * @param $order
     * @param $remark
     * @return bool|string
     */
    public static function backStock($order, $remark='提货发货后台取消发货'){
        Db::startTrans();
        try{
            $user_id = $order['user_id'];
            $goods_id = $order['goods_id'];
            $goods_sku_id = $order['goods_sku_id'];
            $num = $order['goods_num'];

            ##增加库存变动log
            $data = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_sku_id' => $goods_sku_id,
                'balance_stock' => self::getStock($user_id, $goods_sku_id),
                'change_num' => $num,
                'opposite_user_id' => 0,  //发货人id
                'remark' => $remark,
                'change_type' => StockChangeScene::SEND,  //提货发货
                'change_direction' => 10  //增加
            ];
            $res = UserGoodsStockLog::insertData($data);
            if($res === false)throw new Exception('库存返还失败');

            ##减少冻结库存并恢复库存
            $res = self::disFreezeStockByUserGoodsId($user_id, $goods_sku_id, $num, 2, $order['order_no']);
            if($res === false)throw new Exception('库存返还失败1');

            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 获取云库存信息
     * @param $goods_sku_id
     * @return array
     */
    public static function getCloudStock($goods_sku_id){
        $positive = self::where(['goods_sku_id'=>$goods_sku_id, 'stock'=>['GT', 0]])->sum('stock');
        $negative = self::where(['goods_sku_id'=>$goods_sku_id, 'stock'=>['LT', 0]])->sum('stock');
        return compact('positive','negative');
    }

    /**
     * 退款库存处理
     * @param $user_id
     * @param $goods_id
     * @param $goods_sku_id
     * @param $num
     * @param $flag
     * @param $order_no
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function refundStock($user_id, $goods_id, $goods_sku_id, $num, $flag, $order_no){
        $stock_info = self::where(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id])->find();
        ##flag [待发货|待收货 => 2  已完成 => 3]
        self::disFreezeStockByUserGoodsId($user_id, $goods_sku_id, $num, $flag, $order_no);
        $log_data = [
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_sku_id' => $goods_sku_id,
            'balance_stock' => $stock_info['stock'],
            'change_type' => 50,
            'change_num' => $num,
            'change_direction' => 10,
            'remark' => '用户退款',
            'order_no' => $order_no
        ];
        UserGoodsStockLog::insertData($log_data);
    }

    /**
     * 进货退款返还库存
     * @param $user_id
     * @param $goods_id
     * @param $goods_sku_id
     * @param $num
     * @param $order_no
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rebackStock($user_id, $goods_id, $goods_sku_id, $num, $order_no){
        $stock_info = self::where(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id])->find();
        ##flag [待发货|待收货 => 2  已完成 => 3]
        self::update(['history_stock'=>['dec', $num], 'stock'=>['dec', $num]], ['id'=>$stock_info['id']]);
        $log_data = [
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_sku_id' => $goods_sku_id,
            'balance_stock' => $stock_info['stock'],
            'change_type' => 50,
            'change_num' => $num,
            'change_direction' => 10,
            'remark' => '订单退款',
            'order_no' => $order_no
        ];
        UserGoodsStockLog::insertData($log_data);
    }

    /**
     * 代理间转移库存
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function exchangeStock(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $receive_user_id = input('post.receive_user_id',0,'intval');
        $goods_sku_id = input('post.goods_sku_id',0,'intval');
        $stock = input('post.stock',0,'intval');
        $remark = input('post.remark','','str_filter');
        if(!$user_id || !$receive_user_id || !$goods_sku_id || !$stock)throw new Exception('参数缺失');
        ##验证库存
        $stock_info = self::get(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id]);
        if(!$stock_info)throw new Exception('用户库存不足');
        if($stock_info['stock'] < $stock)throw new Exception('用户库存不足');
        ##验证接收库存用户
        $receive_user_info = db('user')->where(['user_id'=>$user_id])->find();
        if(!$receive_user_info)throw new Exception('接收库存用户不存在');
        if($receive_user_info['status'] != 1)throw new Exception('接收库存用户已被冻结');

        $goods_id = $stock_info['goods_id'];
        Db::startTrans();
        try{
            $transfer_stock = 0;
            if($stock_info['transfer_stock'] > 0){
                $transfer_stock = $stock_info['transfer_stock'] >= $stock? $stock : $stock_info['transfer_stock'];
            }
            ##减少用户库存
            $res = self::decStock($stock_info['id'], $stock, $stock_info['transfer_stock'], $user_id, $goods_id, $goods_sku_id,'');
            if($res === false)throw new Exception('操作失败');
            $receive_stock_info = self::get(['user_id'=>$receive_user_id, 'goods_sku_id'=>$goods_sku_id]);
            ##增加接收用户库存
            $res = self::incStock2($receive_user_id, $goods_id, $goods_sku_id, $stock, $transfer_stock);
            if($res === false)throw new Exception('操作失败.');
            ##增加库存变化记录
            $stock_log_data[] = [
                'user_id' => $user_id,
                'goods_id' => $goods_id,
                'goods_sku_id' => $goods_sku_id,
                'balance_stock' => $stock_info['stock'],
                'change_num' => $stock,
                'change_type' => 70,
                'change_direction' => 20,
                'opposite_user_id' => $receive_user_id,
                'remark' => $remark,
            ];
            $stock_log_data[] = [
                'user_id' => $receive_user_id,
                'goods_id' => $goods_id,
                'goods_sku_id' => $goods_sku_id,
                'balance_stock' => $receive_stock_info?$receive_stock_info['stock']:0,
                'change_num' => $stock,
                'change_type' => 70,
                'change_direction' => 10,
                'opposite_user_id' => $user_id,
                'remark' => $remark,
            ];
            $stockLogModel = new UserGoodsStockLog();
            $res= $stockLogModel->saveAll($stock_log_data);
            if($res === false)throw new Exception('操作失败..');
            ##增加转移记录
            $exchange_log_data = compact('user_id','receive_user_id','goods_id','goods_sku_id','stock','remark','transfer_stock');
            $res = (new ExchangeStockLog())->isUpdate(false)->save($exchange_log_data);
            if($res === false)throw new Exception('操作失败...');

            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 销售数据
     * @param $user_id
     * @param $goods_sku_id
     * @return array|bool|false|int[]|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSaleAndStock($user_id, $goods_sku_id){
        $data = self::where(compact('user_id','goods_sku_id'))->field(['history_sale', 'stock'])->find();
        if(!$data){
            return ['history_sale'=>0, 'stock'=>0];
        }
        return $data->toArray();
    }

    /**
     * 老代理迁移数据
     * @return array
     * @throws Exception
     */
    public static function transferData(){
        $model = new self;
        ##迁移总量
        $transfer_total = $model->where(['goods_sku_id'=>$model->main_goods_sku_id])->sum('transfer_stock_history');
        $exchange_stock = ExchangeStockLog::where(['goods_sku_id'=>$model->main_goods_sku_id])->sum('transfer_stock');
        $transfer_total -= $exchange_stock;
        ##已消耗总量
        $rest_transfer_stock = $model->where(['goods_sku_id'=>$model->main_goods_sku_id])->sum('transfer_stock');
        $used_transfer_stock = $transfer_total - $rest_transfer_stock;
        ##迁移用户数
        $transfer_user_num = \app\common\model\User::where(['is_transfer'=>1])->count();
        ##已转化用户数
        $active_transfer_user_num = \app\common\model\User::where(['is_transfer'=>1, 'open_id'=>['<>', '']])->count();
        return compact('transfer_total','used_transfer_stock','transfer_user_num','active_transfer_user_num');
    }

    /**
     * 库存变动明细
     * @return array
     * @throws \think\exception\DbException
     */
    public function userTransferStockLog(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $size = input('post.size',15,'intval');
//        $transfer_data = $this->where(['user_id'=>$user_id, 'goods_sku_id'=>$this->main_goods_sku_id])->field('transfer_stock_history','transfer_stock')->find();
//        $transfer_total = $transfer_data? $transfer_data['transfer_stock_history'] : 0;
//        $transfer_rest = $transfer_data? $transfer_data['transfer_stock'] : 0;
//        $transfer_used = $transfer_total - $transfer_rest;
        $logModel = new UserGoodsStockLog();
        $list = $logModel
            ->where(['user_id'=>$user_id, 'goods_sku_id'=>$this->main_goods_sku_id])
            ->order('create_time','asc')
            ->field(['id', 'balance_stock', 'change_num', 'change_type', 'change_direction', 'create_time', 'remark'])
            ->paginate($size,false);
        $total = $list->total();
        $list = $list->isEmpty() ? [] : $list->toArray()['data'];
        return compact('total','list');
    }

}