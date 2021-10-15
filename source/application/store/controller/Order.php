<?php

namespace app\store\controller;

use app\common\enum\DeliveryType;
use app\store\model\Order as OrderModel;
use app\store\model\Express as ExpressModel;
use app\store\model\OrderDelivery;
use app\store\model\store\shop\Clerk as ShopClerkModel;
use app\store\model\store\Shop as ShopModel;
use think\Exception;

/**
 * 订单管理
 * Class Order
 * @package app\store\controller
 */
class Order extends Controller
{

    /**
     * 待审核发货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function exam_list()
    {
        return $this->getList('待审核订单列表', 'exam');
    }

    /**
     * 待发货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function delivery_list()
    {
        return $this->getList('待发货订单列表', 'delivery');
    }

    /**
     * 待收货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function receipt_list()
    {
        return $this->getList('待收货订单列表', 'receipt');
    }

    /**
     * 待付款订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function pay_list()
    {
        return $this->getList('待付款订单列表', 'pay');
    }

    /**
     * 已完成订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete_list()
    {
        return $this->getList('已完成订单列表', 'complete');
    }

    /**
     * 已取消订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function cancel_list()
    {
        return $this->getList('已取消订单列表', 'cancel');
    }

    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function all_list()
    {
        return $this->getList('全部订单列表', 'all');
    }

    public function order_list(){
        $shopList = ShopModel::getAllList();
        return $this->fetch('index2',compact('shopList'));
    }

    public function getOrderList(){
        $data_type = input('data_type','all','str_filter');
        $delivery_type = input('delivery_type',10,'intval');
        $model = new OrderModel;
        return $this->renderSuccess('','', $model->getList($data_type, $this->request->param(), $delivery_type));
    }

    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        // 物流公司列表
        $expressList = ExpressModel::getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        return $this->fetch('detail', compact(
            'detail',
            'expressList',
            'shopClerkList'
        ));
    }

    public function orderDetail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        // 物流公司列表
        $expressList = ExpressModel::getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        $shopClerkList = $shopClerkList->toArray()['data'];
        return $this->fetch('detail2', compact(
            'detail',
            'expressList',
            'shopClerkList'
        ));
    }

    /**
     * 确认发货
     * @param $order_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delivery($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->delivery($this->postData('order'))) {
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError($model->getError() ?: '发货失败');
    }

    /**
     * 审核发货
     * @param $order_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function examDelivery($order_id){
        $model = OrderModel::detail($order_id);
//        print_r($model->toArray());die;
        if($model->examDelivery($this->postData('exam_delivery'))){
            return $this->renderSuccess('审核成功');
        }
        return $this->renderError($model->getError()?:'审核失败');
    }

    /**
     * 修改订单价格
     * @param $order_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 修改订单物流
     * @param $order_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function updateExpress($order_id){
        $model = OrderModel::detail($order_id);
        if ($model->updateExpress($this->postData('express'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 修改订单物流
     * @param $order_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function updateDeliveryExpress($order_id){
        $model = OrderDelivery::detail($order_id);
        if ($model->updateExpress($this->postData('express'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @param int $deliveryType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getList($title, $dataType, $deliveryType=10)
    {
        // 订单列表
        $model = new OrderModel;
        $list = $model->getList($dataType, $this->request->param(), $deliveryType);
        // 自提门店列表
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('title', 'dataType', 'list', 'shopList'));
    }

    public function getStockList($deliveryType=30){
        // 订单列表
        $model = new OrderModel;
        return $model->getStockList($this->request->param(), $deliveryType);
    }

    /**
     * 提货发货列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function order_delivery(){
        $model = new OrderDelivery();
        $delivery_type = $model->getDeliveryTypeList();
        return $this->fetch('order_delivery2', compact('delivery_type'));
    }

    /**
     * 提货发货数据列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getOrderDeliveryList(){
        $model = new OrderDelivery();
        return $this->renderSuccess('','',$model->getList($this->request->param()));
    }

    /**
     * 确认已自提
     * @param $deliver_id
     * @return array|bool
     */
    public function submitSelfOrder($deliver_id){
        try{
            ##接收参数
            $model = new OrderDelivery();
            $res = $model->submitSelfOrder($deliver_id);
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 取消发货/取消自提
     * @return array|bool
     */
    public function cancelOrder(){
        try{
            ##接收参数
            $deliver_id = input('post.deliver_id',0,'intval');
            $model = new OrderDelivery();
            $res = $model->cancelOrder($deliver_id);
            if(true !== $res)throw new Exception($res);
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 物流订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function deliveryDetail($order_id){
        // 订单详情
        $detail = OrderDelivery::detail($order_id);
        // 物流公司列表
        $expressList = ExpressModel::getAll();

        return $this->fetch('order_delivery_detail2', compact(
            'detail',
            'expressList'
        ));
    }

    /**
     * 物流订单发货
     * @return array|bool
     */
    public function deliverOrderDeliver(){
        try{
            $model = new OrderDelivery();
            $model->deliver($this->request->param());
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 审核发货
     * @return array|bool
     */
    public function examOrderDelivery(){
        try{
            $model = new OrderDelivery();
            if(!$model->examOrderDelivery($this->request->param()))throw new Exception($model->getError());
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 退款
     * @return array|bool
     */
    public function refund(){
        try{
            $model = new OrderModel();
            if(!$model->refund()){throw new Exception($model->getError());};
            return $this->renderSuccess('退款成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 补货订单
     * @return mixed
     */
    public function order_stock(){
//        $shopList = ShopModel::getAllList();
        return $this->fetch('order_stock2');
    }

    /**
     * 补货订单列表
     * @return array
     */
    public function getOrderStockList(){
        return $this->renderSuccess('','',$this->getStockList(DeliveryType::STOCK));
    }

    /**
     * 仓管
     * @return mixed
     */
    public function warehouse(){
        $model = new OrderModel();
        return $this->fetch('',$model->warehouse());
    }

    /**
     * 时间筛选仓库信息
     * @return array
     */
    public function getTimeWarehouseInfo(){
        $model = new OrderModel();
        $data = $model->getTimeNums();
        return $this->renderSuccess('','',$data);
    }

    /**
     * 运费明细
     * @return mixed
     */
    public function freight(){
        return $this->fetch();
    }

}
