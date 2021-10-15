<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class AchievementDetail extends BaseModel
{

    protected $name = 'user_achievement_detail';

    protected $pk = 'id';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id ? : 10001;
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    public function orderInfo(){
        return $this->belongsTo('app\common\model\Order','order_id','order_id');
    }

}