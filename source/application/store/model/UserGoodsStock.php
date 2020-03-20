<?php


namespace app\store\model;

use app\common\model\UserGoodsStock as UserGoodsStockModel;
use think\Db;
use think\Exception;

class UserGoodsStock extends UserGoodsStockModel
{

    /**
     * 获取用户代理商品库存
     * @param int $user_id
     * @param int $goods_id
     * @return int|string
     */
    public static function getUserGoodsStock($user_id=0, $goods_id=0){
        try{
            ##接收参数
            if(!$user_id || !$goods_id){
                $user_id = input('post.user_id', 0,'intval');
                $goods_id = input('post.goods_id', 0,'intval');
            }
            if($user_id <= 0 || $goods_id < 0)throw new Exception('参数错误');

            $stock = self::where([
                    'user_id' => $user_id,
                    'goods_id' => $goods_id
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
     * @param $goods_id
     * @return int|string
     * @throws Exception
     */
    public static function checkDataExist($user_id, $goods_id){
        return (new self)->where([
                'user_id' => $user_id,
                'goods_id' => $goods_id
            ])
            ->value('id');
    }

    /**
     * 更新用户代理商品库存
     * @param $userId
     * @param $goodsId
     * @param $finalStock
     * @param $diffStock
     * @param $changeType
     * @param $remark
     * @param int $oppositeUserId
     * @return bool|string
     */
    public static function updateUserGoodsStock($userId, $goodsId, $finalStock, $diffStock, $changeType, $remark, $oppositeUserId=0){
        Db::startTrans();
        try{
            ##变更库存
            $userGoodsStockId = self::checkDataExist($userId, $goodsId);
            if($userGoodsStockId){
                ##更新
                $res = self::where(['id' => $userGoodsStockId])->setField('stock', $finalStock);
            }else{
                ##新增
                $res = UserGoodsStock::insertUserGoodsStock($userId, $goodsId, $finalStock);
            }
            if($res === false)throw new Exception('库存变更失败');
            ##增加变更记录
            $balanceStock = $finalStock - $diffStock;

            ##获取商品积分信息
            $goodsInfo = Goods::getAgentGoodsInfo($goodsId);
            $res = UserGoodsStockLog::addLog([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'balance_stock' =>$balanceStock,
                'diff_stock' => $diffStock,
                'change_type' => $changeType,
                'remark' => $remark,
                'opposite_user_id' => $oppositeUserId,
                'integral_weight' => $goodsInfo['integral_weight'],
                'wxapp_id' => static::$wxapp_id
            ]);
            if($res === false)throw new Exception('库存变更日志添加失败');
            $stockLogId = (new UserGoodsStockLog)->getLastInsID();

            ##增加用户会员积分&&更新会员等级
            if($diffStock > 0){
                $integralLogId = User::incUserIntegralByGoodsId($userId, $goodsId, $diffStock);
                if(is_string($integralLogId))throw new Exception($integralLogId);
                ##回填库存变更记录的积分变更表id
                if($integralLogId > 0)UserGoodsStockLog::editIntegralLogId(['id'=>$stockLogId, 'integral_log_id'=>$integralLogId]);
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
     * @param $stock
     * @return false|int
     */
    protected static function insertUserGoodsStock($userId, $goodsId, $stock){
        $data = [
            'user_id' => $userId,
            'goods_id' => $goodsId,
            'stock' => $stock,
            'wxapp_id' => static::$wxapp_id
        ];
        return (new self)->save($data);
    }

}