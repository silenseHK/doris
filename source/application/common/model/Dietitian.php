<?php


namespace app\common\model;


class Dietitian extends BaseModel
{

    protected $name = 'dietitian';

    protected $pk = 'dietitian_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','image_id','file_id');
    }

    public function getDescriptionAttr($value){
        return json_decode($value,true);
    }

}