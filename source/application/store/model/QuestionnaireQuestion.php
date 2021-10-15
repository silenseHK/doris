<?php


namespace app\store\model;

use app\common\model\QuestionnaireQuestion as QuestionnaireQuestionModel;

class QuestionnaireQuestion extends QuestionnaireQuestionModel
{

    public function deleteLink($questionnaire_id){
        $this->where(compact('questionnaire_id'))->delete();
    }

    /**
     * 获取问卷分类下的问题
     * @param $questionnaire_id
     * @param $question_cate_id
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateQuestions($questionnaire_id, $question_cate_id){
        return $this->where(compact('questionnaire_id','question_cate_id'))->order('sort','asc')->with(['questions.option'])->select();
    }

    /**
     * 问题展示条件
     * @param $questionnaire_id
     * @param $question_id
     * @return bool|float|mixed|string|null
     */
    public static function getShowLimit($questionnaire_id, $question_id){
        return self::where(compact('questionnaire_id','question_id'))->value('show_limit');
    }

}