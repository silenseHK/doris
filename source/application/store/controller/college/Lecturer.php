<?php


namespace app\store\controller\college;


use app\store\controller\Controller;
use app\store\model\college\Lecturer as LecturerModel;
use think\Exception;

class Lecturer extends Controller
{

    public function index(){
        $model = new LecturerModel();
        return $this->fetch('',$model->index());
    }

    /**
     * 新增
     * @return array|bool|mixed
     */
    public function add(){
        if(request()->isPost()){
            $model = new lecturerModel();
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
     * 编辑
     * @return array|bool|mixed
     */
    public function edit(){
        try{
            $model = new LecturerModel();
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
     * 删除
     * @return array|bool
     */
    public function delete(){
        try{
            $model = new LecturerModel();
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}