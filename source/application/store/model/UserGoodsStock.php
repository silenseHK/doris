<?php


namespace app\store\model;

use app\common\enum\user\StockChangeScene;
use app\common\model\UserGoodsStock as UserGoodsStockModel;
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
            ##减少冻结库存并恢复库存
            $res = self::disFreezeStockByUserGoodsId($user_id, $goods_sku_id, $num, 2);
            if($res === false)throw new Exception('库存返还失败1');
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
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

}