<?php


namespace app\common\model;


class QualitySpecValue extends BaseModel
{

    protected $name = 'quality_spec_value';

    protected $pk = 'spec_value_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id ? : 10001;
    }

}