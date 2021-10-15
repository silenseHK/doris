<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class AchievementLink extends BaseModel
{

    protected $name = 'user_achievement_link';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id ? : 10001;
    }

    public function detail(){
        return $this->belongsTo('app\common\model\user\AchievementDetail','achievement_detail_id','id');
    }

}