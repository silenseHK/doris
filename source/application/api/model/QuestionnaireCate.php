<?php


namespace app\api\model;

use app\common\model\QuestionnaireCate as QuestionnaireCateModel;
use think\db\Query;

class QuestionnaireCate extends QuestionnaireCateModel
{

    /**
     * 问卷分类
     * @param $questionnaire_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function category($questionnaire_id){
        $category = self::where(compact('questionnaire_id'))->with(['cate'=>function(Query $query){$query->field(['cate_id', 'title', 'alias']);}])->field(['question_cate_id'])->order('sort','asc')->select()->toArray();
        return $category;
    }

}