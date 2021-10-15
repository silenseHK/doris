<?php


namespace app\common\model;


class PlatformIncomeLog extends BaseModel
{

    protected $name = 'platform_income_log';

    protected $pk = 'income_id';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    protected $updateTime = false;

    /**
     * 新增平台手指记录
     * @param $data
     * @return int
     */
    public static function addLog($data){
        return (new self)->isUpdate(false)->save($data);
    }

    /**
     * 类型属性
     * @var string[]
     */
    protected static $typeAttr = [
        '10' => [
            'text' => '卖货',
            'value' => 10
        ],
        '20' => [
            'text' => '运费',
            'value' => 20
        ],
        '30' => [
            'text' => '返利',
            'value' => 30
        ]
    ];

    /**
     * 订单类型属性
     * @var array[]
     */
    protected static $orderTypeAttr = [
        '10' => [
            'text' => '消费订单',
            'value' => 10,
        ],
        '20' => [
            'text' => '提货发货',
            'value' => 20,
        ]
    ];

    /**
     * 格式化类型
     * @param $value
     * @return string
     */
    public function getTypeAttr($value){
        return self::$typeAttr[$value];
    }

    /**
     * 格式化订单类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value){
        return self::$orderTypeAttr[$value];
    }

    /**
     * 场景列表
     * @return array[]|string[]
     */
    public function getTypeList(){
        return self::$typeAttr;
    }

}