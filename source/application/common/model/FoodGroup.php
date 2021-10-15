<?php


namespace app\common\model;

class FoodGroup extends BaseModel
{

    protected $name = 'food_group';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ?: 10001;
    }

    protected $typeList = [
        '1' => [
            'title' => 'BMI减脂',
            'value' => 1
        ],
        '2' => [
            'title' => '高尿酸',
            'value' => 2
        ],
        '3' => [
            'title' => '高血糖',
            'value' => 3
        ],
        '4' => [
            'title' => '高血压',
            'value' => 4
        ],
        '5' => [
            'title' => '高血脂',
            'value' => 5
        ],
        '6' => [
            'title' => '老年人',
            'value' => 6
        ],
        '7' => [
            'title' => '素食减脂',
            'value' => 7
        ],
        '8' => [
            'title' => '素食非减脂',
            'value' => 8
        ],
        '9' => [
            'title' => '一般人群食谱',
            'value' => 9
        ],
        '10' => [
            'title' => '增重食谱',
            'value' => 10
        ],
        '11' => [
            'title' => '低血压食谱',
            'value' => 11
        ],
        '12' => [
            'title' => '多囊食谱',
            'value' => 12
        ],
        '13' => [
            'title' => '甲减',
            'value' => 13
        ],
    ];

    public function getTypeList(){
        return $this->typeList;
    }

    /**
     * 格式化类型
     * @param $value
     * @return array
     */
    public function getTypeAttr($value){
        return $this->typeList[$value];
    }

    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','img_id','file_id');
    }

    public static function getUserImg($bmi, $version=2){
        $rule = self::where(['max_bmi'=>['GT', $bmi], 'min_bmi'=>['ELT', $bmi], 'version'=>$version])->order('min_bmi','asc')->with(['image'])->find();
        if(!$rule)return "";
        return $rule['image']['file_path'];
    }

    /**
     * 用户配餐id
     * @param $bmi
     * @param $version //版本
     * @return int
     */
    public static function getUserFoodsGroupId($bmi, $version=1){
        $food_group_id = self::where(['type'=>1, 'max_bmi'=>['GT', $bmi], 'min_bmi'=>['ELT', $bmi], 'version'=>$version])->order('min_bmi','asc')->value('id');
        return (int)$food_group_id;
    }

    public function images(){
        return $this->belongsToMany('app\common\model\UploadFile','app\common\model\FoodGroupImage','image_id','food_group_id')->order('sort','asc');
    }

}