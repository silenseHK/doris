<?php


namespace app\store\controller\store;


use app\store\controller\Controller;
use app\store\model\admin\HandleLog;
use think\Exception;

class Handle extends Controller
{

    public function index(){
        return $this->fetch();
    }

    public function lists(){
        try{
            $list = HandleLog::lists();
            return $this->renderSuccess('','', $list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}