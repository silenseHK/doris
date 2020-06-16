<?php


namespace app\common\model\user;


use app\common\model\BaseModel;
use app\common\model\User;

class Fill extends BaseModel
{

    protected $name = 'user_fill';

    protected $pk = 'fill_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id', 'group_user_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ? : 10001;
    }

    /**
     * 处理团队user_id
     * @param $value
     * @param $data
     * @return int
     */
    public function setGroupUserIdAttr($value, $data){
        return User::getGroupUserId($data['user_id']);
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    public function userAnswer(){
        return $this->hasMany('app\api\model\user\FillAnswer','fill_id','fill_id');
    }

}