<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class OrderDeliver extends BaseModel
{

    protected $name = "order_deliver";

    protected $deliver_type = [
        '10' => [
            'value' => 10,
            'text' => '快递发货'
        ],
        '20' => [
            'value' => 20,
            'text' => '自提'
        ]
    ];

    protected $deliver_status = [
        '10' => [
            'value' => 10,
            'text' => '待发货'
        ],
        '20' => [
            'value' => 20,
            'text' => '已发货'
        ],
        '30' => [
            'value' => 30,
            'text' => '已取消'
        ],
        '40' => [
            'value' => 40,
            'text' => '已完成'
        ]
    ];

    protected $pay_status = [
        '10' => [
            'value' => 10,
            'text' => '待付款'
        ],
        '20' => [
            'value' => 20,
            'text' => '已付款'
        ]
    ];

    protected $completeType = [
        '10' => [
            'value' => 10,
            'text' => '系统操作'
        ],
        '20' => [
            'value' => 20,
            'text' => '后台操作'
        ],
        '30' => [
            'value' => 30,
            'text' => '用户操作'
        ],
    ];

    /**
     * 获取发货类型
     * @param $value
     * @return mixed
     */
    public function getDeliverTypeAttr($value){
        return $this->deliver_type[$value];
    }

    /**
     * 获取发货状态
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getDeliverStatusAttr($value, $data){
        $rtn = $this->deliver_status[$value];
        if($data['deliver_type'] == 20 && $rtn['value'] == 20)$rtn['text'] = "待自提";
        return $rtn;
    }

    /**
     * 获取付款状态
     * @param $value
     * @return mixed
     */
    public function getPayStatusAttr($value){
        return $this->pay_status[$value];
    }

    /**
     * 生成订单号
     * @param $deliverType *发货方式
     * @return string
     */
    public function makeOrderNo($deliverType){
        $orderNo = time() . rand(1000000, 9999999);
        return $deliverType == 10 ? '301' . $orderNo : '302' . $orderNo;
    }

    /**
     * 获取用户信息
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('\app\common\model\User','user_id','user_id');
    }

    /**
     * 获取商品信息
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('\app\common\model\Goods','goods_id','goods_id');
    }

    /**
     * 获取物流信息
     * @return \think\model\relation\BelongsTo
     */
    public function express(){
        return $this->belongsTo('\app\common\model\Express','express_id','express_id');
    }

    /**
     * 获取自提门店信息
     * @return \think\model\relation\BelongsTo
     */
    public function extract(){
        return $this->belongsTo('\app\common\model\store\Shop','extract_shop_id','shop_id');
    }

    /**
     * 一对多 --获取商品规格
     * @return \think\model\relation\BelongsTo
     */
    public function spec(){
        return $this->belongsTo('app\common\model\GoodsSku','goods_sku_id','goods_sku_id');
    }

    /**
     * 库存记录
     * @return \think\model\relation\HasOne
     */
    public function stockLog(){
        return $this->hasOne('app\common\model\UserGoodsStockLog','order_no','order_no');
    }

}