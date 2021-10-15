<?php


namespace app\common\model;

class Entry extends BaseModel
{

    protected $name = 'entry';

    protected $pk = 'entry_id';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    protected $updateTime = false;

}