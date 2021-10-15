<?php


namespace app\store\controller\user;

use app\store\controller\Controller;
use think\Exception;
use app\store\model\Salesperson as SalespersonModel;

class Salesperson extends Controller
{

    public function index(){
        $model = new SalespersonModel();
        return $this->fetch('',['typeList'=>$model->typeList(), 'groupList'=>$model->groupList()]);
    }

    /**
     * 添加招商人员
     * @return array|bool
     */
    public function add(){
        try{
            $model = new SalespersonModel();
            if(!$model->add()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 编辑招商人员
     * @return array|bool
     */
    public function edit(){
        try{
            $model = new SalespersonModel();
            if(!$model->edit()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 删除招商人员
     * @return array|bool
     */
    public function del(){
        try{
            $model = new SalespersonModel();
            if(!$model->del()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 招商人员列表
     * @return array|bool
     */
    public function salespersonList(){
        try{
            $model = new SalespersonModel();
            if(!$data = $model->salespersonList()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','', $data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 修改状态
     * @return array|bool
     */
    public function editStatus(){
        try{
            $model = new SalespersonModel();
            $status = $model->editStatus();
            if($status === false){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','',$status);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function exportSaleData(){
        try{
            $model = new SalespersonModel();
            $status = $model->exportSaleData();
            if($status === false){
                throw new Exception($model->getError());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}