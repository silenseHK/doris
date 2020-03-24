<?php


namespace app\api\model\user;


use app\common\model\UserGoodsStock;
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
        return self::where(['user_id'=> $userId, 'stock'=> ['GT', 0]])
            ->with([
                'goods.image.file'
            ])
            ->field(['stock','goods_id'])
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

}