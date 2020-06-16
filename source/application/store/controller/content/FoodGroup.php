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
        if(request()->isPost()){
            try{
                $model = new FoodGroupModel();
                $model->add();
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch();
        }
    }

    public function edit(){
        try{
            $model = new FoodGroupModel();
            if(request()->isPost()){
                $model = new FoodGroupModel();
                $model->edit();
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

    public function test(){
        return $this->fetch();
    }

}