<?php


namespace app\store\controller\content;


use app\store\controller\Controller;
use app\store\model\OnlineQuestions as OnlineQuestionsModel;
use app\store\model\OnlineQuestionsCate as OnlineQuestionsCateModel;
use think\Exception;

class OnlineQuestions extends Controller
{

    /**
     * 问答列表
     * @return mixed
     */
    public function index(){
        $model = new OnlineQuestionsModel();
        return $this->fetch('', $model->getList());
    }

    /**
     * 添加问答
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(){
        if(request()->isPost()){
            $model = new OnlineQuestionsModel();
            try{
                $model->add();
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            $model = new OnlineQuestionsCateModel();
            return $this->fetch('',['cate_list'=>$model->cateList()]);
        }
    }

    /**
     * 编辑问答
     * @return array|bool|mixed
     */
    public function edit(){
        $model = new OnlineQuestionsModel();
        try{
            if(request()->isPost()){
                $model->edit();
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('',$model->info());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 修改问答状态
     * @return array|bool
     */
    public function changeStatus(){
        try{
            $model = new OnlineQuestionsModel();
            $model->changeStatus();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除问答
     * @return array|bool
     */
    public function delete(){
        try{
            $model = new OnlineQuestionsModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 分类列表
     * @return mixed
     */
    public function cateIndex(){
        $model = new OnlineQuestionsCateModel();
        return $this->fetch('', $model->getList());
    }

    /**
     * 添加分类
     * @return array|bool|mixed
     */
    public function cateAdd(){
        if(request()->isPost()){
            $model = new OnlineQuestionsCateModel();
            try{
                $model->add();
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch();
        }
    }

    /**
     * 编辑分类
     * @return array|bool|mixed
     */
    public function cateEdit(){
        try{
            $model = new OnlineQuestionsCateModel();
            if(request()->isPost()){
                $model->edit();
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('',$model->info());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 编辑分类状态
     * @return array|bool
     */
    public function cateStatusChange(){
        try{
            $model = new OnlineQuestionsCateModel();
            $model->changeStatus();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function cateDelete(){
        try{
            $model = new OnlineQuestionsCateModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}