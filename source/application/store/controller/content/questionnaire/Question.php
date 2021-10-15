<?php


namespace app\store\controller\content\questionnaire;


use app\store\controller\Controller;

use app\store\model\Question as QuestionModel;
use app\store\model\QuestionCate;
use think\Exception;

class Question extends Controller
{

    public function index(){
        $model = new QuestionModel();
        return $this->fetch('', $model->getIndexData());
    }

    public function questions(){
        $model = new QuestionModel();
        return $this->fetch('', $model->getIndexData());
    }

    public function add(){
        $model = new QuestionModel();
        if($this->request->isPost()){
            try{
                $res = $model->add();
                if($res !== true)throw new Exception($res);
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('', ['typeList'=>$model->getTypeList()]);
        }
    }

    public function edit(){
        try{
            $model = new QuestionModel();
            if($this->request->isPost()){
                $res = $model->edit();
                if($res !== true)throw new Exception($res);
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('', $model->info());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function del(){
        try{
            $model = new QuestionModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 问题分类列表
     * @return mixed
     */
    public function cateIndex(){
        return $this->fetch();
    }

    /**
     * 问题分类数据
     * @return array|bool
     */
    public function getCateIndexData(){
        try{
            $model = new QuestionCate();
            $data = $model->getCateIndexData();
            return $this->renderSuccess('','',$data);
        }catch(Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 添加问题分类
     * @return array|bool
     */
    public function addCate(){
        try{
            $model = new QuestionCate();
            $model->add();
            return $this->renderSuccess('添加成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 编辑问题分类
     * @return array|bool
     */
    public function editCate(){
        try{
            $model = new QuestionCate();
            $model->editCate();
            return $this->renderSuccess('修改成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除问题分类
     * @return array|bool
     */
    public function delCate(){
        try{
            $model = new QuestionCate();
            $model->delCate();
            return $this->renderSuccess('删除成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 分类列表
     * @return array|bool
     */
    public function cateList(){
        try{
            $model = new QuestionCate();
            $list = $model->getCateList();
            return $this->renderSuccess('','', $list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 问题列表
     * @return array|bool
     */
    public function questionList(){
        try{
            $model = new QuestionModel();
            $list = $model->getQuestionList();
            return $this->renderSuccess('','', $list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}