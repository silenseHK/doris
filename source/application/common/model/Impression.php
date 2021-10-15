<?php


namespace app\common\model;


class Impression extends BaseModel
{

    protected $name = 'impression';

    protected $updateTime = false;

    protected $pk = 'impression_id';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

}