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

    public function getStockList($title, $deliveryType=30){
        // 订单列表
        $model = new OrderModel;
        $list = $model->getStockList($this->request->param(), $deliveryType);
//        print_r($list->toArray());die;
        // 自提门店列表
        $shopList = ShopModel::getAllList();
        return $this->fetch('order_stock', compact('title', 'dataType', 'list', 'shopList'));
    }

    /**
     * 提货发货列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function order_delivery(){
        $model = new OrderDelivery();
        $data = $model->makeData($this->request->param());
        return $this->fetch('order_delivery', $data);
    }

    /**
     * 确认已自提
     * @return array|bool
     */
    public function submitSelfOrder(){
        try{
            ##接收参数
            $deliver_id = input('post.deliver_id',0,'intval');
            $model = new OrderDelivery();
            $model->submitSelfOrder($deliver_id);
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

        return $this->fetch('order_delivery_detail', compact(
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

    public function order_stock(){
        return $this->getStockList('补货订单', DeliveryType::STOCK);
    }

}
