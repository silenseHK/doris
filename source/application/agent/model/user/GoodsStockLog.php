<?php


namespace app\agent\model\user;

use app\common\enum\user\StockChangeScene;
use app\common\model\UserGoodsStockLog;

class GoodsStockLog extends UserGoodsStockLog
{

    /**
     * 获取器 --格式化库存变化数量
     * @param $value
     * @param $data
     * @return int
     */
    public function getChangeNumAttr($value, $data){
        return $data['change_direction'] == 20 ? -$value : $value;
    }

    /**
     * 获取器 --格式化库存变化场景
     * @param $value
     * @return mixed
     */
    public function getChangeTypeAttr($value){
        return StockChangeScene::data()[$value];
    }

    /**
     * 获取器 -- 格式化收货人
     * @param $value
     * @param $data
     * @return array
     */
    public function getOppositeUserAttr($value, $data){
        if($data['change_type'] == StockChangeScene::BUY && !$data['opposite_user_id'])return ['nickName'=>'公司','grade'=>['name'=>'平台']];
        return $value;
    }

}