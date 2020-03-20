<?php


namespace app\common\model;

use app\api\model\Goods as GoodsModel;

class UserGoodsStock extends BaseModel
{

    protected $name = 'user_goods_stock';

    protected $autoWriteTimestamp = false;

    /**
     * 获取用户代理商品库存
     * @param $userId
     * @param $goodsId
     * @return int
     */
    public static function getStock($userId, $goodsId){
        $stock = self::where(['user_id'=>$userId, 'goods_id'=>$goodsId])->value('stock');
        return $stock ? : 0;
    }

    /**
     * 检查代理商品库存
     * @param $user
     * @param $goodsId
     * @param $num
     * @return array
     */
    public static function checkStock($user, $goodsId, $num){
        ##获取上级供应商
        $supplyUserId = User::getSupplyGoodsUser($user['user_id'], $goodsId, $num);
        $isStockEnough = $supplyUserId ? 1 : GoodsModel::checkAgentGoodsStock($goodsId, $num);
        return compact('supplyUserId','isStockEnough');
    }

    /**
     * 检查用户代理商品数据是否存在
     * @param $user_id
     * @param $goods_id
     * @return int|string
     */
    public static function checkExist($user_id, $goods_id){
        return (new self)->where([
                'user_id' => $user_id,
                'goods_id' => $goods_id
            ])
            ->value('id');
    }

    /**
     * 增加库存
     * @param $id
     * @param $stock
     * @return int|true
     * @throws \think\Exception
     */
    public static function incStock($id, $stock){
        return (new self)->where(['id'=>$id])->setInc('stock', $stock);
    }

    /**
     * 减少库存
     * @param $id
     * @param $stock
     * @return int|true
     * @throws \think\Exception
     */
    public static function decStock($id, $stock){
        return (new self)->where(['id'=>$id])->setDec('stock', $stock);
    }

    /**
     * 插入库存数据
     * @param $data
     * @return false|int
     */
    public static function insertData($data){
        $data['wxapp_id'] = self::$wxapp_id;
        return (new self)->isUpdate(false)->save($data);
    }

    /**
     * 编辑库存
     * @param $userId
     * @param $goodsId
     * @param $stock
     * @param $direction
     * @throws \think\Exception
     */
    public static function editStock($userId, $goodsId, $stock, $direction='inc'){
        $stockId = self::checkExist($userId, $goodsId);
        if($stockId){ ##修改
            $direction == 'inc' ? self::incStock($stockId, $stock) : self::decStock($stockId, $stock);
        }else{ ##添加
            UserGoodsStock::insertData([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'stock' => $stock
            ]);
        }
    }

    /**
     * 通过user_id 和 goods_id 增加用户的库存
     * @param $userId
     * @param $goodsId
     * @param $stock
     * @return int|true
     * @throws \think\Exception
     */
    public static function incStockByUserGoodsId($userId, $goodsId, $stock){
        return self::where(['user_id'=>$userId, 'goods_id'=>$goodsId])->setInc('stock', $stock);
    }

}