<?php


namespace app\store\model;

Use app\common\model\UserGoodsStockLog as UserGoodsStockLogModel;

class UserGoodsStockLog extends UserGoodsStockLogModel
{

    /**
     * 写入地理商品库存变更记录
     * @param $options
     * @return false|int
     */
    public static function addLog($options){
        ##处理库存改变数量
        $options['change_num'] = abs($options['diff_stock']);
        ##处理库存改变方向
        $options['change_direction'] = $options['diff_stock'] > 0 ? self::$CHANGE_DIRECTION['UP'] : self::$CHANGE_DIRECTION['DOWN'];
        ##处理库存改变类型
        $options['change_type'] = isset($options['change_type']) ? self::$CHANGE_TYPE[$options['change_type']] : self::$CHANGE_TYPE['USER'];
        return (new self)->allowField(true)->save($options);
    }

    /**
     * 回填integral_log_id
     * @param $data
     * @return false|int
     */
    public static function editIntegralLogId($data){
        return (new self)->isUpdate()->save($data);
    }

}