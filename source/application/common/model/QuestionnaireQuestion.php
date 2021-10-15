<?php


namespace app\common\model;


use think\model\Pivot;

class QuestionnaireQuestion extends Pivot
{

    protected $name = 'questionnaire_question';

    protected $autoWriteTimestamp = false;

    public function questions(){
        return $this->hasMany('app\common\model\Question','question_id','question_id');
    }

}