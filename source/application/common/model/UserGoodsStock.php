<?php


namespace app\common\model;

use app\api\model\Goods as GoodsModel;

use app\api\model\User as UserModel;
use app\api\model\user\TransferStockLog;
use app\common\model\user\Grade;
use app\common\validate\TransferValid;
use think\Db;
use think\db\Query;
use think\Exception;

class UserGoodsStock extends BaseModel
{

    protected $name = 'user_goods_stock';

    protected $autoWriteTimestamp = false;

    protected $insert = ['wxapp_id'];

    /**
     * 设置wxapp_id
     * @return mixed
     */
    public function setWxappIdAttr(){
        return static::$wxapp_id ? : 10001;
    }

    /**
     * 获取用户代理商品库存
     * @param $userId
     * @param $goodsSkuId
     * @return int
     */
    public static function getStock($userId, $goodsSkuId){
        $stock = self::where(['user_id'=>$userId, 'goods_sku_id'=>$goodsSkuId])->value('stock');
        return $stock ? : 0;
    }

    /**
     * 检查代理商品库存
     * @param $user
     * @param $goodsId
     * @param $goods_sku_id
     * @param $num
     * @return array
     */
    public static function checkStock($user, $goodsId, $goods_sku_id, $num){
        ##获取上级供应商
        $supply_info = User::getSupplyGoodsUser($user['user_id'], $goodsId, $num);
        $supplyUserId = $supply_info['supply_user_id'];
        $grade_id = $supply_info['grade_id'];
        $supply_user_grade_id = $supplyUserId?UserModel::getUserGrade($supplyUserId):0;
        $isStockEnough = $supplyUserId ? 1 : GoodsModel::checkAgentGoodsStock($goods_sku_id, $num);
        return compact('supplyUserId','isStockEnough', 'grade_id','supply_user_grade_id');
    }

    /**
     * @param $user //用户信息
     * @param $goodsId //商品id
     * @param $goods_sku_id //商品规格id
     * @param $num //商品数量
     * @param int $is_force_platform //是否强制平台出货 1是 0否
     * @param int $is_achieve //是否需要正常增加业绩 1是 0否
     * @param int $is_integral //是否需要正常升级 1是 0否
     * @return array
     * @throws Exception
     */
    public static function checkStock2($user, $goodsId, $goods_sku_id, $num, $is_force_platform=0, $is_achieve=1, $is_integral=1){
        $grade_info = User::getBuyGoodsGrade2($goodsId, $num);

        ##出货人
        if($is_force_platform){
            $applyGradeIds = [];
        }else{
            if($user['grade']['weight'] >= $grade_info['weight']){
                $weight = $user['grade']['weight'];
            }else{
                $weight = $grade_info['weight'];
            }
            $applyGradeIds = Grade::getApplyGrade2($weight);
        }

        ##升级
        if($is_integral){
            if($user['grade']['weight'] >= $grade_info['weight']){
                $grade_id = $user['grade_id'];
            }else{
                $grade_id = $grade_info['grade_id'];
            }
        }else{
            $grade_id = $user['grade_id'];
        }

        ##业绩
        $is_achievement = 10;
        if($is_achieve){
            $weight = Grade::getWeightByGradeId($grade_id);
            if($weight > 10){
                if($user['grade']['weight'] >= $grade_info['weight'] && $is_integral){
                    $is_achievement = 30;
                }else{
                    $is_achievement = 20;
                }
            }
        }

        ##获取上级供应商
//        if($is_force_platform){ ##后台使用 强制平台出货
//            $applyGradeIds = [];
//            $grade_id = $user['grade_id'];
//            $weight = $user['grade']['weight'];
//            $is_achievement = 10;
//            if($is_achieve && $weight > 10){
//                $is_achievement = 30;
//            }
//        }else{
//            $grade_info = User::getBuyGoodsGrade2($goodsId, $num);
//            $is_achievement = 10;
//            if($user['grade']['weight'] >= $grade_info['weight']){
//                $weight = $user['grade']['weight'];
//                $grade_id = $user['grade_id'];
//                if($weight > 10 && $is_achieve){
//                    $is_achievement = 30;
//                }
//            }else{
//                $weight = $grade_info['weight'];
//                $grade_id = $grade_info['grade_id'];
//                if($weight > 10 && $is_achieve){
//                    $is_achievement = 20;
//                }
//            }
//            $applyGradeIds = Grade::getApplyGrade2($weight);
//        }

        if(empty($applyGradeIds)){
            $supplyUserId = 0;
        }else{
            $relation = trim($user['relation'],'-');
            $relation = explode('-', $relation);
            if($relation && $relation[0]){
                ##获取供应人id
                $relation_ids = implode(',', $relation);
                $relation_ids = trim($relation_ids,',');
                $user_id = User::where(['user_id'=>['IN', $relation], 'grade_id'=>['IN', $applyGradeIds], 'is_delete'=>0, 'status'=>1])->orderRaw("field(user_id," . $relation_ids . ")")->value('user_id');
                $supplyUserId = $user_id ? : 0;
            }else{
                $supplyUserId = 0;
            }
        }
        $supply_user_grade_id = $supplyUserId?UserModel::getUserGrade($supplyUserId):0;
        $isStockEnough = $supplyUserId ? 1 : GoodsModel::checkAgentGoodsStock($goods_sku_id, $num);
        return compact('supplyUserId','isStockEnough', 'grade_id', 'supply_user_grade_id', 'is_achievement');
    }

    /**
     * 检查用户代理商品数据是否存在
     * @param $user_id
     * @param $goodsSkuId
     * @return int|string
     */
    public static function checkExist($user_id, $goodsSkuId){
        return (new self)->where([
                'user_id' => $user_id,
                'goods_sku_id' => $goodsSkuId
            ])
            ->value('id');
    }

    /**
     * 检查用户代理商品数据是否存在 返回剩余迁移库存
     * @param $user_id
     * @param $goodsSkuId
     * @return array|bool|false|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkExist2($user_id, $goodsSkuId){
        return (new self)->where([
            'user_id' => $user_id,
            'goods_sku_id' => $goodsSkuId
        ])
            ->field(['id', 'transfer_stock'])
            ->find();
    }

    /**
     * 增加库存
     * @param $id
     * @param $stock
     * @return int|true
     * @throws \think\Exception
     */
    public static function incStock($id, $stock){
        return (new self)->where(['id'=>$id])->update(['stock'=>['inc',$stock],'history_stock'=>['inc',$stock]]);
    }

    /**
     * 减少库存
     * @param $id
     * @param $stock
     * @param $transfer_stock
     * @param $userId
     * @param $goodsId
     * @param $goodsSkuId
     * @param $order_no
     * @return int|true
     * @throws \think\Exception
     */
    public static function decStock($id, $stock, $transfer_stock, $userId, $goodsId, $goodsSkuId, $order_no){
        if($transfer_stock > 0){
            $dec = ($transfer_stock >= $stock) ? $stock : $transfer_stock;
            ##增加迁移库存变化记录表
            $model = new TransferStockLog();
            $data = [
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'order_no' => $order_no,
                'balance_stock' => $transfer_stock,
                'stock' => $dec
            ];
            $model->isUpdate(false)->save($data);
            return (new self)->where(['id'=>$id])->update(['stock'=>['dec', $stock], 'history_sale'=>['inc', $stock], 'transfer_stock'=>['dec', $dec]]);
        }
        return (new self)->where(['id'=>$id])->update(['stock'=>['dec', $stock], 'history_sale'=>['inc', $stock]]);
    }

    /**
     * 增加库存[可增加迁移库存]
     * @param $user_id
     * @param $goods_id
     * @param $goods_sku_id
     * @param $stock
     * @param $transfer_stock
     * @return UserGoodsStock|bool|false|int
     * @throws \think\exception\DbException
     */
    public static function incStock2($user_id, $goods_id, $goods_sku_id, $stock, $transfer_stock){
        $log = self::get(compact('user_id','goods_sku_id'));
        if($log){
            if($transfer_stock > 0){
                return (new self)
                    ->where(['id'=>$log['id']])
                    ->update(
                        [
                            'stock' => ['inc', $stock],
                            'history_stock' => ['inc', $stock],
                            'transfer_stock' => ['inc', $transfer_stock],
                            'transfer_stock_history' => ['inc', $transfer_stock]
                        ]
                    );
            }
            return (new self)
                ->where(['id'=>$log['id']])
                ->update(
                    [
                        'stock' => ['inc', $stock],
                        'history_stock' => ['inc', $stock]
                    ]
                );
        }else{
            ##新增数据
            $log_data = compact('user_id','goods_sku_id', 'goods_id', 'stock', 'transfer_stock');
            $log_data = array_merge($log_data, [
                'history_stock' => $stock,
                'transfer_stock_history' => $transfer_stock
            ]);
            return (new self)->isUpdate(false)->save($log_data);
        }

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
     * @param $goodsSkuId
     * @param $stock
     * @param $order_no
     * @param $direction
     * @throws \think\Exception
     */
    public static function editStock($userId, $goodsId, $goodsSkuId, $stock, $order_no, $direction='inc'){
        $stockInfo = self::checkExist2($userId, $goodsSkuId);
        if($stockInfo){ ##修改
            $stockId = $stockInfo['id'];
            $direction == 'inc' ? self::incStock($stockId, $stock) : self::decStock($stockId, $stock, $stockInfo['transfer_stock'], $userId, $goodsId, $goodsSkuId, $order_no);
        }else{ ##添加
            if($direction == 'dec')$stock = -$stock;
            UserGoodsStock::insertData([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'history_sale' => abs($stock),
                'stock' => $stock
            ]);
        }
    }

    /**
     * 新增库存并且和历史库存
     * @param $userId
     * @param $goodsId
     * @param $goodsSkuId
     * @param $stock
     */
    public static function incHistoryStock($userId, $goodsId, $goodsSkuId, $stock){
        $stockId = self::checkExist($userId, $goodsSkuId);
        if($stockId){ ##更新
            $where = ['id'=>$stockId];
            self::update(['stock'=>['inc', $stock], 'history_stock'=>['inc', $stock]], $where);
        }else{ ##新增
            UserGoodsStock::insertData([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'stock' => $stock,
                'history_stock' => $stock
            ]);
        }
    }

    /**
     * 新增迁移库存
     * @param $userId
     * @param $goodsId
     * @param $goodsSkuId
     * @param $stock
     */
    public static function incTransferHistoryStock($userId, $goodsId, $goodsSkuId, $stock){
        $stockId = self::checkExist($userId, $goodsSkuId);
        if($stockId){ ##更新
            $where = ['id'=>$stockId];
            self::update(['stock'=>['inc', $stock], 'history_stock'=>['inc', $stock], 'transfer_stock'=>$stock, 'transfer_stock_history'=>$stock], $where);
        }else{ ##新增
            UserGoodsStock::insertData([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'stock' => $stock,
                'history_stock' => $stock,
                'transfer_stock' => $stock,
                'transfer_stock_history' => $stock
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
     * @param $goods_sku_id
     * @param $stock
     * @param $order_no
     * @param $flag
     * @return int|true
     * @throws \think\Exception
     */
    public static function freezeStockByUserGoodsId($user_id, $goods_id, $goods_sku_id, $stock, $order_no, $flag=0){
        $stockInfo = self::checkExist2($user_id, $goods_sku_id);
        $res = false;
        if($stockInfo){ ##更新
            $where = ['id' => $stockInfo['id']];
            switch($flag){
                case 0:  ##单纯冻结商品
                    $res = self::where($where)->setInc('freeze_stock', $stock);
                    break;
                case 1: ##冻结商品 并且 减少可用库存
                    $transfer_stock = $stockInfo['transfer_stock'];
                    if($transfer_stock > 0){
                        $dec = ($transfer_stock >= $stock) ? $stock : $transfer_stock;
                        ##增加迁移库存变化记录表
                        $model = new TransferStockLog();
                        $data = [
                            'user_id' => $user_id,
                            'goods_id' => $goods_id,
                            'goods_sku_id' => $goods_sku_id,
                            'order_no' => $order_no,
                            'balance_stock' => $transfer_stock,
                            'stock' => $dec
                        ];
                        $model->isUpdate(false)->save($data);
                        $res = self::update(['freeze_stock'=>['inc', $stock], 'stock'=>['dec', $stock], 'transfer_stock'=>['dec', $dec]], $where);
                    }else{
                        $res = self::update(['freeze_stock'=>['inc', $stock], 'stock'=>['dec', $stock]], $where);
                    }
                    break;
            }
        }else{ ##新增
            $data = compact('user_id','goods_id','goods_sku_id');
            $data['stock'] = -$stock;
            if($flag == 1){
                $data['freeze_stock'] = $stock;
            }
            $res = (new self)->isUpdate(false)->save($data);
        }
        return $res;
    }

    /**
     * 通过user_id 和goods_id 减少冻结商品数量
     * @param $user_id
     * @param $goods_sku_id
     * @param $stock
     * @param $flag
     * @param $order_no
     * @return int|true
     * @throws \think\Exception
     */
    public static function disFreezeStockByUserGoodsId($user_id, $goods_sku_id, $stock, $flag=0, $order_no=''){
        $res = false;
        $where = compact('user_id','goods_sku_id');
        ##查找迁移库存变化记录
        $transfer_stock = 0;
        if($order_no){
            $transfer_data = TransferStockLog::where(['order_no'=>$order_no])->find();
            if($transfer_data)$transfer_stock = $transfer_data['balance_stock'];
        }
        switch($flag){
            case 0: ##单纯减少冻结库存
                $res = self::where($where)->setDec('freeze_stock', $stock);
                break;
            case 1: ##减少冻结库存 增加历史出库库存
                $res = self::update(['freeze_stock'=>['dec', $stock], 'history_sale'=> ['inc', $stock]], $where);
                break;
            case 2: ##减少冻结库存 增加现有库存
                if($transfer_stock){
                    TransferStockLog::destroy($transfer_data['id']);
                    $res = self::update(['freeze_stock'=>['dec', $stock], 'stock'=> ['inc', $stock], 'transfer_stock'=>$transfer_stock], $where);
                }else{
                    $res = self::update(['freeze_stock'=>['dec', $stock], 'stock'=> ['inc', $stock]], $where);
                }
                break;
            case 3: ##减少历史出库 增加现有库存
                if($transfer_stock){
                    TransferStockLog::destroy($transfer_data['id']);
                    $res = self::update(['history_sale'=>['dec', $stock], 'stock'=> ['inc', $stock], 'transfer_stock'=>$transfer_stock], $where);
                }else{
                    $res = self::update(['history_sale'=>['dec', $stock], 'stock'=> ['inc', $stock]], $where);
                }
                break;
        }
        return $res;
    }

    /**
     * 统计用户负库存数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public static function countNegativeStock($user_id){
        return self::where(['user_id'=>$user_id, 'stock'=>['LT', 0]])->count('id');
    }

    /**
     * 一对多 --获取商品信息
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

    /**
     * 一对多 --获取商品规格
     * @return \think\model\relation\BelongsTo
     */
    public function spec(){
        return $this->belongsTo('app\common\model\GoodsSku','goods_sku_id','goods_sku_id');
    }

    /**
     * 增加代理库存
     * @param $user_id
     * @param $goods_id
     * @param $goods_sku_id
     * @param $stock
     * @param $remark
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function incTransferAgentStock($user_id, $goods_id, $goods_sku_id, $stock, $remark=''){
        ##增加库存
        self::incTransferHistoryStock($user_id, $goods_id, $goods_sku_id, $stock);
        $goods_sku = GoodsSku::get($goods_sku_id);
        ##获取用户库存
        $balance_stock = self::where(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id])->value('stock');
        $balance_stock = $balance_stock?:0;
        ##增加库存记录
        $remark = $remark?:'老代理库存转移';
        $log_data = [
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_sku_id' => $goods_sku_id,
            'balance_stock' => $balance_stock,
            'change_num' => $stock,
            'change_type' => 60,
            'change_direction' => 10,
            'remark' => $remark
        ];
        $res = UserGoodsStockLog::insertData($log_data);
        if($res === false)return '库存变动记录添加失败';
        ##减少商品库存
        $res = GoodsSku::decStock($goods_sku, $stock);
        if(is_string($res))return $res;
        return true;
    }

    public static function fileIncTransferAgentStock($user_id, $goods_id, $goods_sku_id, $stock, $remark=''){
        ##增加库存
        self::incTransferHistoryStock($user_id, $goods_id, $goods_sku_id, $stock);
//        $goods_sku = GoodsSku::get($goods_sku_id);
        ##获取用户库存
        $balance_stock = self::where(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id])->value('stock');
        $balance_stock = $balance_stock?:0;
        ##增加库存记录
        $remark = $remark?:'老代理库存转移';
        $log_data = [
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_sku_id' => $goods_sku_id,
            'balance_stock' => $balance_stock,
            'change_num' => $stock,
            'change_type' => 60,
            'change_direction' => 10,
            'remark' => $remark
        ];
        $res = UserGoodsStockLog::insertData($log_data);
        if($res === false)return '库存变动记录添加失败';
        return true;
    }

    /**
     * 批量迁移库存
     * @param $arr
     * @param $goods_sku_id
     * @param $remark
     * @return bool|string
     */
    public static function fileTransferStock($arr, $goods_sku_id, $remark){
        Db::startTrans();
        try{
            ##商品信息
            $goodsSku = GoodsSku::get($goods_sku_id);
            if(!$goodsSku)throw new Exception('商品数据不存在');

            $validate = new TransferValid();
            $total_stock = 0;
            foreach($arr as $k => $v){
                if(!$validate->scene('file_transfer_stock')->check($v)){
                    throw new Exception("openid=>{$v['openid']}:" . $validate->getError());
                }
                if($v['stock'] > 0){
                    $total_stock += $v['stock'];
                }

            }
            ##判断库存
            if($goodsSku['stock_num'] < $total_stock)throw new Exception('商品库存不足');
            ##减少库存
            $res = GoodsSku::decStock($goodsSku, $total_stock);
            if(is_string($res))return $res;

            ##增加用户库存
            foreach($arr as $kk => $vv){
                $user_id = User::where(['ws_openid'=>trim($vv['openid'])])->value('user_id');
                if(!$user_id)throw new Exception("用户{$vv['openid']}不存在");
                if($vv['stock'] > 0){
                    $res = self::fileIncTransferAgentStock($user_id, $goodsSku['goods_id'], $goods_sku_id, $vv['stock'], $remark);
                    if(is_string($res))throw new Exception($res);
                }
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

    }

}