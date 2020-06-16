<?php


namespace app\common\model;

use app\common\model\GoodsSku;
use think\Exception;

class GoodsStockLog extends BaseModel
{

    protected $name = 'goods_stock_log';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ? : 10001;
    }

    protected $type_list = [
        '10' => [
            'value' => 10,
            'text' => '创建商品'
        ],
        '20' => [
            'value' => 20,
            'text' => '修改库存'
        ],
        '30' => [
            'value' => 30,
            'text' => '平台出货'
        ],
        '40' => [
            'value' => 40,
            'text' => '后台操作用户库存'
        ],
    ];

    /**
     * 添加记录
     * @param $goods_sku_id
     * @param $change_type
     * @param $change_direction
     * @param $num
     * @param string $order_no
     * @param int $user_id
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addLog($goods_sku_id, $change_type, $change_direction, $num, $order_no='', $user_id=0){
        $sku_data = self::getSkuData($goods_sku_id);
        $goods_id = $sku_data['goods_id'];
        $balance_num = $change_type==10? 0 : $sku_data['stock_num'];
        $data = compact('goods_sku_id','goods_id','change_type','change_direction','num','balance_num','order_no','user_id');
        $res = (new self)->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
        return true;
    }

    /**
     * 获取商品sku信息
     * @param $goods_sku_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSkuData($goods_sku_id){
        return GoodsSku::where(['goods_sku_id'=>$goods_sku_id])->field(['goods_id', 'stock_num'])->find();
    }

}