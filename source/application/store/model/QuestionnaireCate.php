<?php


namespace app\store\model;

use app\common\model\QuestionnaireCate as QuestionnaireCateModel;

class QuestionnaireCate extends QuestionnaireCateModel
{

    public function deleteLink($questionnaire_id){
        $this->where(compact('questionnaire_id'))->delete();
    }

}