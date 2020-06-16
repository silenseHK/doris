<?php


namespace app\store\controller\user;


use app\store\controller\Controller;
use app\store\model\college\CollegeClass as CollegeClassModel;
use app\store\model\OrderDelivery;
use app\store\model\user\BalanceLog;
use think\Exception;
use app\store\model\Order as OrderModel;

class Order extends Controller
{

    public function index(){
        $user_id = input('user_id',0,'intval');
        return $this->fetch('',compact('user_id'));
    }

    /**
     * 普通订单列表
     * @return array|bool
     */
    public function orderList(){
        try{
            $model = new BalanceLog();
            return $this->renderSuccess('','',$model->getUserBalanceList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 发货订单列表
     * @return array|bool
     */
    public function deliveryOrderList(){
        try{
            $model = new OrderDelivery();
            return $this->renderSuccess('','',$model->getUserOrderList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 微信支付订单列表
     * @return array|bool
     */
    public function wxOrderList(){
        try{
            $model = new OrderModel();
            return $this->renderSuccess('','',$model->getUserWxPayOrderList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 获取普通订单运费信息
     * @return array|bool
     */
    public function orderFreight(){
        try{
            $model = new OrderModel();
            return $this->renderSuccess('','', $model->getOrderFreight());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 获取提货发货订单运费明细
     * @return array|bool
     */
    public function deliveryFreight(){
        try{
            $model = new OrderDelivery();
            return $this->renderSuccess('','', $model->getDeliveryFreight());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}