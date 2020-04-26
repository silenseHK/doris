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

    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','img_id','file_id');
    }

    public static function getUserImg($bmi){
        $rule = self::where(['max_bmi'=>['GT', $bmi], 'min_bmi'=>['ELT', $bmi]])->order('min_bmi','asc')->with(['image'])->find();
        if(!$rule)return "";
        return $rule['image']['file_path'];
    }

}