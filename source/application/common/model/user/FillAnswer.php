<?php


namespace app\common\model\user;


use app\common\model\BaseModel;

class FillAnswer extends BaseModel
{

    protected $name = 'user_fill_answer';

    protected $pk = 'fill_answer_id';

    protected $autoWriteTimestamp = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ?: 10001;
    }

    public function question(){
        return $this->belongsTo('app\common\model\Question','question_id','question_id');
    }

    public function getAnswerMarkAttr($value){
        $value = trim($value,'-');
        if(!$value)return [];
        return explode('-',$value);
    }

}