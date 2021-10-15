<?php


namespace app\common\model;


use traits\model\SoftDelete;

class Questionnaire extends BaseModel
{

    protected $insert = ['wxapp_id'];

    protected $name = 'questionnaire';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    public function setWxappIdAttr(){
        return static::$wxapp_id ? : 10001;
    }

    public function questions(){
        return $this->belongsToMany('app\common\model\Question','app\common\model\QuestionnaireQuestion','question_id','questionnaire_id')->order('sort','asc');
    }

    public function cate(){
        return $this->belongsToMany('app\common\model\QuestionCate','app\common\model\QuestionnaireCate','question_cate_id','questionnaire_id')->order('sort','asc');
    }

}