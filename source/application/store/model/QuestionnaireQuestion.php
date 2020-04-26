<?php


namespace app\store\model;

use app\common\model\QuestionnaireQuestion as QuestionnaireQuestionModel;

class QuestionnaireQuestion extends QuestionnaireQuestionModel
{

    public function deleteLink($questionnaire_id){
        $this->where(compact('questionnaire_id'))->delete();
    }

}