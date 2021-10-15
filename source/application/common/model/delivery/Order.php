<?php


namespace app\common\model\delivery;


use app\common\enum\DeliveryOrderStatus;
use app\common\model\BaseModel;

class Order extends BaseModel
{

    protected $name = 'delivery_order';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    /**
     * 订单状态
     * @param $value
     * @return array|mixed
     */
    public function getDeliveryStatusAttr($value){
        return DeliveryOrderStatus::data()[$value];
    }

    /**
     * 发货时间
     * @param $value
     * @return array
     */
    public function getDeliveryTimeAttr($value){
        $date = $value? date('Y-m-d H:i:s', $value) : '--';
        return [
            'date' => $date,
            'value' => $value
        ];
    }

    /**
     * 备货时间
     * @param $value
     * @return array
     */
    public function getWaitDeliveryTimeAttr($value){
        $date = $value? date('Y-m-d H:i:s', $value) : '--';
        return [
            'date' => $date,
            'value' => $value
        ];
    }

    /**
     * 电子面单html
     * @param $value
     * @return string
     */
    public function getExpressHtmlAttr($value){
        return stripslashes(htmlspecialchars_decode($value));
    }

    /**
     * 取消时间
     * @param $value
     * @return array
     */
    public function getCancelTimeAttr($value){
        $date = $value? date('Y-m-d H:i:s', $value) : '--';
        return [
            'date' => $date,
            'value' => $value
        ];
    }

    /**
     * 规格图片
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('app\common\model\UploadFile', 'file_id', 'goods_image');
    }

    /**
     * 物流公司
     * @return \think\model\relation\BelongsTo
     */
    public function express()
    {
        return $this->belongsTo('app\common\model\Express','express_id','express_id');
    }

}