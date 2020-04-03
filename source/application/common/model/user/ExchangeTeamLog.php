<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class ExchangeTeamLog extends BaseModel
{

    protected $name = "user_exchange_team_log";

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    /**
     * 设置小程序id
     * @return mixed
     */
    public function setWxappIdAttr(){
        return static::$wxapp_id;
    }

    /**
     * 关联新邀请人
     * @return \think\model\relation\BelongsTo
     */
    public function newInvitation(){
        return $this->belongsTo('app\common\model\User','new_invitation_user_id','user_id');
    }

    /**
     * 关联老邀请人
     * @return \think\model\relation\BelongsTo
     */
    public function oldInvitation(){
        return $this->belongsTo('app\common\model\User','old_invitation_user_id','user_id');
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

}