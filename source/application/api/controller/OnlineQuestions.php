<?php


namespace app\api\controller;

use app\api\model\OnlineQuestionsCate as OnlineQuestionsCateModel;
use app\api\model\OnlineQuestions as OnlineQuestionsModel;
use think\Exception;

class OnlineQuestions extends Controller
{

    /**
     * 问答分类列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cateList(){
        $model = new OnlineQuestionsCateModel();
        return $this->renderSuccess(['list'=>$model->getList()]);
    }

    /**
     * 问答列表
     * @return array
     */
    public function answerList(){
        try{
            $model = new OnlineQuestionsModel();
            return $this->renderSuccess($model->getList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 问答详情
     * @return array
     */
    public function answerDetail(){
        try{
            $model = new OnlineQuestionsModel();
            return $this->renderSuccess($model->info());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}