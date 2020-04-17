<?php


namespace app\store\controller\wxapp;


use app\store\controller\Controller;
use app\store\model\NoticeMessage;

class SystemMsg extends Controller
{

    public function index(){
        $model = new NoticeMessage();
        return $this->fetch('',$model->getSystemLists());
    }

    public function add(){
        if(request()->isPost()){

        }else{
            return $this->fetch();
        }
    }

}