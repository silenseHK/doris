<?php


namespace app\api\model\user;

use app\common\model\UserGoodsStockLog;

class GoodsStockLog extends UserGoodsStockLog
{

    /**
     * 计算用户进货量
     * @param $user_id
     * @param $start
     * @param $end
     * @return float|int
     */
    public static function countBuy($user_id, $start, $end){
        return self::where([
                'user_id' => $user_id,
                'change_type' => 10,
                'change_direction' => 10,
                'create_time' => ['BETWEEN', [$start, $end]]
            ])
            ->sum('change_num');
    }

}