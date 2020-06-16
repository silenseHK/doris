<?php


namespace app\store\controller\content;


use app\api\model\user\Fill;
use app\store\controller\Controller;
use app\store\model\Questionnaire as QuestionnaireModel;
use app\store\model\Question as QuestionModel;
use think\Exception;

class Questionnaire extends Controller
{

    public function index(){
        $model = new QuestionnaireModel();
        return $this->fetch('',$model->getIndexData());
    }

    public function add(){
        if(request()->isPost()){
            try{
                $model = new QuestionnaireModel();
                $res = $model->add();
                if($res !== true)throw new Exception($res);
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('');
        }
    }

    public function edit(){
        try{
            $model = new QuestionnaireModel();
            if(request()->isPost()){
                $res = $model->edit();
                if($res !== true)throw new Exception($res);
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('',$model->info());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function del(){
        try{
            $model = new QuestionnaireModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function questions(){
        $model = new QuestionModel();
        return $this->renderSuccess('','',$model->getIndexData());
    }

    public function userFillList(){
        $model = new Fill();
        return $this->fetch('',$model->getIndexData());
    }

    public function fillList(){
        $model = new Fill();
        return $this->fetch('',$model->getFillList());
    }

    public function userFillDetail(){
        $model = new Fill();
        return $this->fetch('',$model->userFillDetail());
    }

}