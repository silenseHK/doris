<?php


namespace app\common\model\user;

use app\common\model\BaseModel;

class ManageRewardLog extends BaseModel
{

    protected $name = "manage_reward_log";

    protected $insert = ['wxapp_id'];

    protected $updateTime = false;

    public function setWxappIdAttr(){
        return static::$wxapp_id;
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods(){
        return $this->belongsTo('app\common\model\Goods','goods_id','goods_id');
    }

}