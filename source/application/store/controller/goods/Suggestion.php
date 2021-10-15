<?php


namespace app\store\controller\goods;


use app\store\controller\Controller;
use app\store\model\GoodsSuggestion;
use think\Exception;

class Suggestion extends Controller
{

    public function index(){
        return $this->fetch();
    }

    /**
     * 套餐列表
     * @return array|bool
     */
    public function suggestionList(){
        try{
            $model = new GoodsSuggestion();
            $list = $model->suggestionList();
            if(!$list)throw new Exception($model->getError());
            return $this->renderSuccess('','', $list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 新增
     * @return array|bool
     */
    public function add(){
        try{
            $model = new GoodsSuggestion();
            if(!$model->add()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 编辑
     * @return array|bool
     */
    public function edit(){
        try{
            $model = new GoodsSuggestion();
            if(!$model->edit()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 修改字段
     * @return array|bool
     */
    public function editField(){
        try{
            $model = new GoodsSuggestion();
            if(!$model->editField()){
                throw new Exception($model->getError());
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
            $model = new GoodsSuggestion();
            if(!$model->del()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}