<?php


namespace app\api\controller\user;


use app\api\controller\Controller;
use app\api\model\Bank;
use think\Exception;
use app\api\model\user\BankCard as UserBankCardModel;

class BankCard extends Controller
{

    /**
     * 用户银行卡列表
     * @return array
     */
    public function lists(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            return $this->renderSuccess($model->getList($this->request->post()));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 银行列表
     * @return array
     */
    public function banks(){
        try{
            $user = $this->getUser();
            $model = new Bank();
            return $this->renderSuccess([
                'list' => $model->banks()
            ]);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户添加银行卡
     * @return array
     */
    public function add(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            $res = $model->add($this->request->post());
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 银行卡详情
     * @return array
     */
    public function detail(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            return $this->renderSuccess($model->detail($this->request->param()));
        }catch(Exception $e){
           return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户修改银行卡
     * @return array
     */
    public function edit(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            $res = $model->edit($this->request->post());
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户删除银行卡
     * @return array
     */
    public function del(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            $res = $model->del($this->request->post());
            if(!$res)throw new Exception("操作失败");
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 设置默认银行卡
     * @return array
     */
    public function setDefault(){
        try{
            $user = $this->getUser();
            $model = new UserBankCardModel($user);
            $res = $model->setDefault($this->request->post());
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}