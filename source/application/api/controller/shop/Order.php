<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\Setting as SettingModel;
use app\api\model\user\OrderDeliver;
use app\common\service\Order as OrderService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\model\store\shop\Clerk as ClerkModel;
use app\store\model\OrderDelivery;
use think\Exception;

/**
 * 自提订单管理
 * Class Order
 * @package app\api\controller\shop
 */
class Order extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 核销订单详情
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $model = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $clerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
        return $this->renderSuccess([
            'order' => $model,  // 订单详情
            'clerkModel' => $clerkModel,
            'setting' => [
                // 积分名称
                'points_name' => SettingModel::getPointsName(),
            ],
        ]);
    }

    /**
     * 确认核销
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function extract($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $order = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $ClerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
        if (!$ClerkModel->checkUser($order['extract_shop_id'])) {
            return $this->renderError($ClerkModel->getError());
        }
        // 确认核销
        if ($order->verificationOrder($ClerkModel['clerk_id'])) {
            return $this->renderSuccess([], '订单核销成功');
        }
        return $this->renderError($order->getError() ?: '核销失败');
    }

    /**
     * 核销提货发货自提订单
     * @param $deliver_id
     * @return array
     */
    public function extractDeliver($deliver_id){
        try{
            $order = OrderDeliver::get(compact('deliver_id'));
            // 验证是否为该门店的核销员
            $ClerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
            if (!$ClerkModel->checkUser($order['extract_shop_id'])) {
                return $this->renderError($ClerkModel->getError());
            }
            ##执行核销
            (new OrderDelivery)->submitSelfOrder($deliver_id,30);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}