<?php


namespace app\store\controller\college;


use app\store\controller\Controller;
use app\store\model\college\CollegeClass as CollegeClassModel;
use think\Exception;
use app\store\model\college\CollegeClassCode as CollegeClassCodeModel;

class CollegeClass extends Controller
{

    public function index(){
        $model = new CollegeClassModel();
        return $this->fetch('', $model->index());
    }

    /**
     * 新增
     * @return array|bool|mixed
     */
    public function add(){
        if(request()->isPost()){
            try{
                $model = new CollegeClassModel();
                if(!$model->add())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            $lesson_id = input('lesson_id',0,'intval');
            if($lesson_id < 0)return $this->renderError('参数错误');
            return $this->fetch('',compact('lesson_id'));
        }
    }

    /**
     * 编辑
     * @return array|bool|mixed
     */
    public function edit(){
        try{
            $model = new CollegeClassModel();
            if(request()->isPost()){
                if(!$model->edit())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('', $model->editInfo());
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
            $model = new CollegeClassModel();
            if(!$model->del())throw new Exception($model->getError());
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 修改字段
     * @return array|bool
     */
    public function changeField(){
        try{
            $model = new CollegeClassModel();
            $res = $model->changeField();
            return $this->renderSuccess('操作成功','', $res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 新增私享码
     * @return array|bool
     */
    public function addCode(){
        try{
            $model = new CollegeClassCodeModel();
            if(!$model->add())throw new Exception($model->getError());
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除私享码
     * @return array|bool
     */
    public function delCode(){
        try{
            $model = new CollegeClassCodeModel();
            if(!$model->del())throw new Exception($model->getError());
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 私享码列表
     * @return array|bool
     */
    public function codeList(){
        try{
            $model = new CollegeClassCodeModel();
            return $this->renderSuccess('','',$model->getCodeList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}