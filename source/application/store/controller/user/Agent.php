<?php


namespace app\store\controller\user;


use app\store\controller\Controller;
use app\store\model\User;
use think\Exception;
use app\store\model\Agent as AgentModel;

class Agent extends Controller
{

    public function index(){
        return $this->fetch();
    }

    public function agentList(){
        try{
            $model = new AgentModel();
            if(!$data = $model->agentList()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','', $data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function add(){
        try{
            $model = new AgentModel();
            if(!$model->add()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function edit(){
        try{
            $model = new AgentModel();
            if(!$model->edit()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function searchAgent(){
        try{
            $model = new User();
            if(!$res = $model->searchAgent()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','',$res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function editStatus(){
        try{
            $model = new AgentModel();
            $res = $model->editStatus();
            if(is_bool($res)){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功','',$res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function editPwd(){
        try{
            $model = new AgentModel();
            $res = $model->editPwd();
            if(!$res){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功','',$res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}