<?php


namespace app\store\model;

use app\common\model\QuestionOptions as QuestionOptionsModel;

class QuestionOptions extends QuestionOptionsModel
{

    /**
     * 删除option
     * @param $question_id
     */
    public function delOption($question_id){
        $this->where(compact('question_id'))->delete();
    }

}