<?php


namespace app\agent\controller;


use app\agent\logic\OrderLogic;
use app\common\library\aes\Aes;
use think\Exception;
use think\Request;

class Order extends Base
{
    protected $logic;

    public function __construct(Aes $aes, OrderLogic $orderLogic, Request $request = null)
    {
        parent::__construct($aes, $request);
        $this->logic = $orderLogic;
    }

    /**
     * 个人消费补货订单
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function orderList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->orderList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 个人提货发货订单
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function deliveryList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->deliveryList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 团队消费补货订单
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function teamOrderList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->teamOrderList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 团队提货发货订单
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function teamDeliveryList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->teamDeliveryList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}