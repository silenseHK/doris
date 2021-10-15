<?php


namespace app\common\model;


class QualitySpec extends BaseModel
{

    protected $name = 'quality_spec';

    protected $pk = 'spec_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id ? : 10001;
    }

    public function specList(){
        return $this->hasMany('app\common\model\QualitySpecValue','spec_id','spec_id');
    }

}