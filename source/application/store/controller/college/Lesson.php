<?php


namespace app\store\controller\college;


use app\store\controller\Controller;
use app\store\model\college\CollegeClassCode;
use app\store\model\college\LessonCate;
use think\Exception;
use app\store\model\college\Lesson as LessonModel;

class Lesson extends Controller
{

    /**
     * 分类列表
     * @return mixed
     */
    public function cateIndex(){
        $model = new LessonCate();
        return $this->fetch('',$model->getList());
    }

    /**
     * 添加分类
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cateAdd(){
        $model = new LessonCate();
        if(request()->isPost()){
            try{
                $model->add();
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('',['cate_list'=>$model->getParentCateList()]);
        }
    }

    /**
     * 编辑分类
     * @return array|bool|mixed
     */
    public function cateEdit(){
        $model = new LessonCate();
        try{
            if(request()->isPost()){
                $model->edit();
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('',$model->getInfo());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除分类
     * @return array|bool
     */
    public function cateDelete(){
        $model = new LessonCate();
        try{
            $model->del();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 列表
     * @return mixed
     */
    public function index(){
        $model = new LessonModel();
        return $this->fetch('',$model->index());
    }

    /**
     * 新增
     * @return array|bool|mixed
     */
    public function add(){
        $model = new LessonModel();
        if(request()->isPost()){
            try{
                if(!$model->add())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('add1',$model->addInfo());
        }
    }

    /**
     * 编辑
     * @return array|bool|mixed
     */
    public function edit(){
        $model = new LessonModel();
        try{
            if(request()->isPost()){
                if(!$model->edit())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }else{
                return $this->fetch('edit1',$model->editInfo());
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
            $model = new LessonModel();
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
            $model = new CollegeClassCode();
            return $this->renderSuccess('','',$model->getLessonCodeList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 添加课程私享码
     * @return array|bool
     */
    public function addCode(){
        try{
            $model = new CollegeClassCode();
            $model->addCode();
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
            $model = new LessonModel();
            $res = $model->changeField();
            return $this->renderSuccess('操作成功','',$res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}