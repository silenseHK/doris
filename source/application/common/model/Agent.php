<?php


namespace app\common\model;


class Agent extends BaseModel
{

    protected $name = 'agent';

    protected $pk = 'agent_id';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    protected $updateTime = false;

    public function getLoginTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

}