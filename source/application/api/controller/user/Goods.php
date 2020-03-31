<?php


namespace app\api\controller\user;


use app\api\controller\Controller;
use app\api\model\User;
use app\api\model\user\OrderDeliver;
use think\Exception;

class Goods extends Controller
{

    /**
     * 提货发货商品列表
     * @return array
     */
    public function lists(){
        try{
            $user = $this->getUser();
            $model = new User();
            return $this->renderSuccess($model->getGoodsSendLists($user, $this->request->post()));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 提货发货商品库存和用户收货地址
     * @return array
     */
    public function index(){
        try{
            $user = $this->getUser();
            $model = new User();
            return $this->renderSuccess($model->getGoodsSendData($user, $this->request->post()));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 提交发货申请
     * @return array
     */
    public function apply(){
        try{
            $user = $this->getUser();
            $model = new OrderDeliver($user);
            $res = $model->apply($this->request->post());
            if(!is_array($res))throw new Exception($res);
            return $this->renderSuccess($res,'提货申请提交成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 计算运费
     * @return array
     */
    public function countFreight(){
        try{
            $user = $this->getUser();
            $model = new OrderDeliver($user);
            return $this->renderSuccess([
                'freight'=>$model->getFreight($this->request->post())
            ]);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 获取提货发货订单列表
     * @return array
     */
    public function orderLists(){
        try{
            $user = $this->getUser();
            $model = new OrderDeliver();
            return $this->renderSuccess($model->orderList($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 确认收货
     * @return array
     */
    public function complete(){
        try{
            $user = $this->getUser();
            $model = new OrderDeliver();
            $res = $model->complete($user);
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 订单详情
     * @return array
     */
    public function detail(){
        try{
            $user = $this->getUser();
            $model = new OrderDeliver();
            return $this->renderSuccess($model->detail($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}