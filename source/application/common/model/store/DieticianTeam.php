<?php


namespace app\common\model\store;


use app\common\model\BaseModel;

class DieticianTeam extends BaseModel
{

    protected $name = "dietician_team";

    protected $autoWriteTimestamp = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

}