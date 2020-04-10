<?php


namespace app\api\model\user;


use app\common\model\UserGoodsStock;
use Exception;
use think\Db;
use think\db\Query;

class GoodsStock extends UserGoodsStock
{

    /**
     * 获取可提货发货商品列表
     * @param $userId
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSendLists($userId){
        return self::where(['user_id'=> $userId])
            ->with([
                'goods',
                'spec' => function(Query $query){
                    $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with(['image'=>function(Query $query){$query->field(['file_id', 'file_name', 'storage']);}]);
                }
            ])
            ->field(['stock','goods_id','goods_sku_id'])
            ->select();
    }

    /**
     * 商品信息
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\api\model\Goods','goods_id','goods_id')->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
    }

    /**
     * 获取用户本月进货量
     * @param $user_id
     * @return float|int
     */
    public static function countMonthBuy($user_id){
        $start = get_month_start_timestamp();
        $end = get_month_end_timestamp();
        return GoodsStockLog::countBuy($user_id, $start, $end);
    }

    /**
     * 获取用户今日进货量
     * @param $user_id
     * @return float|int
     */
    public static function countDayBuy($user_id){
        $start = get_day_start_timestamp();
        $end = get_day_end_timestamp();
        return GoodsStockLog::countBuy($user_id, $start, $end);
    }

    /**
     * 获取用户上月进货量
     * @param $user_id
     * @return float|int
     */
    public static function countLastMonthBuy($user_id){
        $start = get_last_month_start_timestamp();
        $end = get_last_month_end_timestamp();
        return GoodsStockLog::countBuy($user_id, $start, $end);
    }

    /**
     * 检查用户云库存是否为正
     * @param $user_id
     * @return bool
     */
    public static function checkAllStock($user_id){
        $count = self::where(['user_id'=>$user_id, 'stock'=>['LT', 0]])->count('id');
        return $count == 0;
    }

    /**
     * 提货发货提交申请
     * @param $order
     * @return bool|string
     */
    public static function takeStock($order){
        $user_id = $order['user_id'];
        $num = $order['goods_num'];
        $goods_id = $order['goods_id'];
        $goods_sku_id = $order['goods_sku_id'];
        $stock = $order['stock'];
        try{
            ##减库存 添加冻结库存
            if(self::freezeStockByUserGoodsId($user_id, $goods_id, $goods_sku_id, $num,1) === false)throw new Exception('提交申请失败');
            ##添加库存变更记录
            $stockLogData = [
                'user_id' => $user_id,
                'goods_sku_id' => $goods_sku_id,
                'goods_id' => $goods_id,
                'balance_stock' => $stock,
                'change_num' => $num,
                'remark' => '提货发货',
                'change_type' => 30,
                'change_direction' => 20
            ];
            if((new GoodsStockLog)->isUpdate(false)->save($stockLogData) === false)throw new Exception('提交申请失败');
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

}