<?php


namespace app\common\model;

use app\common\enum\user\StockChangeScene;

class UserGoodsStockLog extends BaseModel
{

    protected $updateTime = false;

    protected $name = 'user_goods_stock_log';

    protected $insert = ['wxapp_id'];

    /**
     * 库存改变类型(10.用户改变 20.后台操作)
     * @var array
     */
    static $CHANGE_TYPE = [
        'USER' => 10,
        'ADMIN' => 20
    ];

    /**
     * 库存改变方向(10.增加 20.减少)
     * @var array
     */
    static $CHANGE_DIRECTION = [
        'UP' => 10,
        'DOWN' => 20
    ];

    /**
     * 修改器  设置wxapp_id
     * @return mixed
     */
    public function setWxappIdAttr(){
        return self::$wxapp_id ? : '10001';
    }

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
     * 插入库存修改记录
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public static function insertAllData($data){
        return (new self)->isUpdate(false)->saveAll($data);
    }

    /**
     * 插入单条库存修改记录
     * @param $data
     * @return false|int
     */
    public static function insertData($data){
        return (new self)->isUpdate(false)->save($data);
    }

    /**
     * 一对多 --商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

    /**
     * 一对多 --用户
     * @return \think\model\relation\BelongsTo
     */
    public function oppositeUser(){
        return $this->belongsTo('app\common\model\User','opposite_user_id','user_id');
    }

    /**
     * 获取指定时间段的进货量
     * @param $goods_id
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public function getBuyStock($goods_id, $user_id, $start_time, $end_time){
        $where = compact('goods_id','user_id');
        $where['change_type'] = 40;
        $where['change_direction'] = 10;
        $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        return $this->where($where)->sum('change_num');
    }

    /**
     * 获取指定时间段多用户的进货总量
     * @param $goods_id
     * @param $user_ids
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public function getUsersStock($goods_id, $user_ids, $start_time, $end_time){
        $where = compact('goods_id');
        $where['change_type'] = 40;
        $where['change_direction'] = 10;
        $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        $where['user_id'] = ['IN', $user_ids];
        return $this->where($where)->sum('change_num');
    }

    /**
     * 积分记录
     * @return \think\model\relation\BelongsTo
     */
    public function integralLog(){
        return $this->belongsTo('app\common\model\user\IntegralLog','integral_log_id','log_id');
    }

}