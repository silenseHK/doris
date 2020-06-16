<?php


namespace app\common\model;


use traits\model\SoftDelete;

class OnlineQuestions extends BaseModel
{

    protected $name = 'online_questions';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return static::$wxapp_id ?: 10001;
    }

    public function cate(){
        return $this->belongsTo('app\common\model\OnlineQuestionsCate','cate_id','cate_id');
    }

    /**
     * 处理解答内容
     * @param $value
     * @return string
     */
    public function getAnswerAttr($value){
        return htmlspecialchars_decode($value);
    }

}