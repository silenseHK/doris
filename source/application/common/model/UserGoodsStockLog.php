<?php


namespace app\common\model;

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

}