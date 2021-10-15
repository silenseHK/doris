<?php


namespace app\store\controller\content;


use app\store\controller\Controller;
use app\store\model\FoodGroup as FoodGroupModel;
use think\Exception;

class FoodGroup extends Controller
{

    public function index(){
        $model = new FoodGroupModel();
        return $this->fetch('',$model->getIndexData());
    }

    public function add(){
        $model = new FoodGroupModel();
        if(request()->isPost()){
            try{
                if(!$model->add())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('',['typeList'=>$model->getTypeList()]);
        }
    }

    public function edit(){
        try{
            $model = new FoodGroupModel();
            if(request()->isPost()){
                $model = new FoodGroupModel();
                if(!$model->edit())throw new Exception($model->getError());
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
            $model = new FoodGroupModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}