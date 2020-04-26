<?php


namespace app\store\controller\content\questionnaire;


use app\store\controller\Controller;

use app\store\model\Question as QuestionModel;
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

}