<?php


namespace app\store\controller\content;


use app\store\controller\Controller;
use app\store\model\Qrcode as QrcodeModel;
use think\Exception;

class Qrcode extends Controller
{

    public function index(){
        return $this->fetch();
    }

    public function add(){
        if(request()->isPost()){
            try{
                $model = new QrcodeModel();
                if(!$model->add())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch();
        }
    }

    public function edit(){
        $model = new QrcodeModel();
        if(request()->isPost()){
            try{
                $model = new QrcodeModel();
                if(!$model->edit())throw new Exception($model->getError());
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            $info = $model->getInfo();
            return $this->fetch('',$info);
        }
    }

    public function lists(){
        $model = new QrcodeModel();
        $list = $model->getLists();
        return $this->renderSuccess('','',$list);
    }

    /**
     * 删除二维码
     * @return array|bool
     */
    public function del(){
        $model = new QrcodeModel();
        if($model->del()){
            return $this->renderSuccess('操作成功');
        }else{
            return $this->renderError('操作失败');
        }
    }

    /**
     * 编辑属性
     * @param QrcodeModel $model
     * @return array|bool
     */
    public function editField(QrcodeModel $model){
        try{
            $data = $model->editField();
            if($model->getError())throw new Exception($model->getError());
            return $this->renderSuccess('操作成功','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}