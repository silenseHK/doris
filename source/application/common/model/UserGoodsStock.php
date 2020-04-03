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
        $supply_info = User::getSupplyGoodsUser($user['user_id'], $goodsId, $num);
        $supplyUserId = $supply_info['supply_user_id'];
        $grade_id = $supply_info['grade_id'];
        $isStockEnough = $supplyUserId ? 1 : GoodsModel::checkAgentGoodsStock($goodsId, $num);
        return compact('supplyUserId','isStockEnough', 'grade_id');
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
     * 新增库存并且和历史库存
     * @param $userId
     * @param $goodsId
     * @param $stock
     */
    public static function incHistoryStock($userId, $goodsId, $stock){
        $stockId = self::checkExist($userId, $goodsId);
        echo $stockId;
        if($stockId){ ##更新
            $where = ['id'=>$stockId];
            self::update(['stock'=>['inc', $stock], 'history_stock'=>['inc', $stock]], $where);
        }else{ ##新增
            UserGoodsStock::insertData([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'stock' => $stock,
                'history_stock' => $stock
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

    /**
     * 通过user_id 和 goods_id 减少用户的库存
     * @param $userId
     * @param $goodsId
     * @param $stock
     * @return int|true
     * @throws \think\Exception
     */
    public static function decStockByUserGoodsId($userId, $goodsId, $stock){
        return self::where(['user_id'=>$userId, 'goods_id'=>$goodsId])->setDec('stock', $stock);
    }

    /**
     * 通过user_id 和goods_id 增加冻结商品数量
     * @param $user_id
     * @param $goods_id
     * @param $stock
     * @param $flag
     * @return int|true
     * @throws \think\Exception
     */
    public static function freezeStockByUserGoodsId($user_id, $goods_id, $stock, $flag=0){
        $where = compact('user_id','goods_id');
        $res = false;
        switch($flag){
            case 0:  ##单纯冻结商品
                $res = self::where($where)->setInc('freeze_stock', $stock);
                break;
            case 1: ##冻结商品 并且 减少可用库存
                $res = self::update(['freeze_stock'=>['inc', $stock], 'stock'=>['dec', $stock]], $where);
                break;
        }
        return $res;
    }

    /**
     * 通过user_id 和goods_id 减少冻结商品数量
     * @param $user_id
     * @param $goods_id
     * @param $stock
     * @param $flag
     * @return int|true
     * @throws \think\Exception
     */
    public static function disFreezeStockByUserGoodsId($user_id, $goods_id, $stock, $flag=0){
        $res = false;
        $where = compact('user_id','goods_id');
        switch($flag){
            case 0: ##单纯减少冻结库存
                $res = self::where($where)->setDec('freeze_stock', $stock);
                break;
            case 1: ##减少冻结库存 增加历史出库库存
                $res = self::update(['freeze_stock'=>['dec', $stock], 'history_sale'=> ['inc', $stock]], $where);
                break;
            case 2: ##减少冻结库存 增加现有库存
                $res = self::update(['freeze_stock'=>['dec', $stock], 'stock'=> ['inc', $stock]], $where);
                break;
        }
        return $res;
    }

    /**
     * 一对多 --获取商品信息
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

}