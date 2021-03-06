<?php


namespace app\store\controller\wxapp;


use app\store\controller\Controller;
use app\store\model\NoticeMessage;
use think\Exception;

class SystemMsg extends Controller
{

    /**
     * 列表
     * @return mixed
     */
    public function index(){
        $model = new NoticeMessage();
        return $this->fetch('',$model->getSystemLists());
    }

    /**
     * 新增
     * @return array|bool|mixed
     */
    public function add(){
        if(request()->isPost()){
            try{
                $model = new NoticeMessage();
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
     * 编辑
     * @return array|bool|mixed
     */
    public function edit(){
        $model = new NoticeMessage();
        try{
            if(request()->isPost()){
                $model = new NoticeMessage();
                $model->edit();
            }else{
                return $this->fetch('',$model->info());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除
     * @return array|bool
     */
    public function del(){
        try{
            $model = new NoticeMessage();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}