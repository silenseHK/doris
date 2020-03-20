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
        return static::$wxapp_id;
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

}