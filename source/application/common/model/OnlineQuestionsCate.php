<?php


namespace app\common\model;


use traits\model\SoftDelete;

class OnlineQuestionsCate extends BaseModel
{

    protected $name = 'online_questions_cate';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $hidden = ['delete_time', 'update_time'];

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ?: 10001;
    }

    public function icon(){
        return $this->belongsTo('app\common\model\UploadFile','icon_id','file_id');
    }

}