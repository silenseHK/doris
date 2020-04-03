<?php

namespace app\api\model;

use app\api\service\order\PaySuccess;
use app\common\model\Order as OrderModel;

use app\api\model\User as UserModel;
use app\api\model\Goods as GoodsModel;
use app\api\model\Setting as SettingModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\OrderGoods as OrderGoodsModel;
use app\api\model\dealer\Order as DealerOrderModel;
use app\api\service\Payment as PaymentService;

use app\common\library\helper;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\order\PayStatus as PayStatusEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\service\wechat\wow\Order as WowService;
use app\common\service\order\Complete as OrderCompleteService;
use app\common\exception\BaseException;
use think\db\Query;

/**
 * 订单模型
 * Class Order
 * @package app\api\model
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'update_time'
    ];

    /**
     * 待支付订单详情
     * @param $orderNo
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getPayDetail($orderNo)
    {
        return self::get(['order_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0], ['goods', 'user']);
    }

    /**
     * 订单支付事件
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function onPay($payType = PayTypeEnum::WECHAT)
    {
        ##更新发货人
        $this->updateSupplyUser();
        // 判断商品状态、库存
        if (!$this->checkGoodsStatusFromOrder($this['goods'])) {
            return false;
        }
        // 余额支付
        if ($payType == PayTypeEnum::BALANCE) {
            return $this->onPaymentByBalance($this['order_no']);
        }
        return true;
    }

    /**
     * 更新订单（出品人 价格 支付金额）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateSupplyUser(){
        $goodsList = $this['goods'];
        $agentGoods = [];
        foreach($goodsList as $goods){
            if(!isset($agentGoods[$goods['goods_id']]))$agentGoods[$goods['goods_id']] = 0;
            $agentGoods[$goods['goods_id']] += $goods['total_num'];
        }
        if(!empty($agentGoods)){
            ##获取最新的供货人
            $agentData= User::repayGetSupplyGoodsUser($this['user_id'], $agentGoods);
            if($this['supply_user_id'] != $agentData['supplyUserId']){
                ##更新出货人
                $this->where(['order_id'=>$this['order_id']])->setField('supply_user_id', $agentData['supplyUserId']);
            }
            ##更新规格价格
            if(!empty($agentData['goodsData'])){
                $goodsData = $agentData['goodsData'];
                foreach($goodsList as $k => $v){
                    if(isset($goodsData[$v['goods_id']]) && $goodsData[$v['goods_id']] != $v['goods_price']){
                        ##更新价格
                        OrderGoods::editPrice($v['order_goods_id'], $goodsData[$v['goods_id']], $agentGoods[$v['goods_id']]);
                        $this['goods'][$k]['total_price'] = $goodsData[$v['goods_id']] * $agentGoods[$v['goods_id']];
                    }
                }
            }
        }
        ##计算最新价格
        $payPrice = 0;
        foreach($this['goods'] as $v){
            $payPrice += $v['total_price'];
        }
        $this['pay_price'] = $payPrice + $this['express_price'];
        return true;
    }

    /**
     * 构建支付请求的参数
     * @param $user
     * @param $order
     * @param $payType
     * @return array
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function onOrderPayment($user, $order, $payType)
    {
        if ($payType == PayTypeEnum::WECHAT) {
            return $this->onPaymentByWechat($user, $order);
        }
        return [];
    }

    /**
     * 构建微信支付请求
     * @param $user
     * @param $order
     * @return array
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function onPaymentByWechat($user, $order)
    {
        return PaymentService::wechat(
            $user,
            $order['order_id'],
            $order['order_no'],
            $order['pay_price'],
            OrderTypeEnum::MASTER
        );
    }

    /**
     * 立即购买：获取订单商品列表
     * @param $goodsId
     * @param $goodsSkuId
     * @param $goodsNum
     * @param $user
     * @return array
     */
    public function getOrderGoodsListByNow($goodsId, $goodsSkuId, $goodsNum, $user)
    {
        // 商品详情
        /* @var GoodsModel $goods */
        $goods = GoodsModel::detail($goodsId);
        // 商品sku信息
        $goods['goods_sku'] = GoodsModel::getGoodsSku($goods, $goodsSkuId);
        // 商品列表
        $goodsList = [$goods->hidden(['category', 'content', 'image', 'sku'])];
        foreach ($goodsList as &$item) {
            // 商品单价
            if($item['sale_type'] == 1){##层级代理
                $item['goods_price'] = UserModel::getAgentGoodsPrice($user['user_id'],$goodsId,$goodsNum);
            }else{
                $item['goods_price'] = $item['goods_sku']['goods_price'];
            }
            // 商品购买数量
            $item['total_num'] = $goodsNum;
            // 商品购买总金额
            $item['total_price'] = helper::bcmul($item['goods_price'], $goodsNum);
        }
        return $goodsList;
    }

    /**
     * 余额支付标记订单已支付
     * @param $orderNo
     * @return bool
     * @throws \think\exception\DbException
     */
    public function onPaymentByBalance($orderNo)
    {
        // 获取订单详情
        $PaySuccess = new PaySuccess($orderNo);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(PayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $PaySuccess->getError();
        }
        return $status;
    }

    /**
     * 用户中心订单列表
     * @param $user_id
     * @param string $type
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = PayStatusEnum::PENDING;
                $filter['order_status'] = 10;
                break;
            case 'delivery';
                $filter['pay_status'] = PayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = PayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'comment';
                $filter['is_comment'] = 0;
                $filter['order_status'] = 30;
                break;
        }
        return $this->with(['goods.image'])
            ->where('user_id', '=', $user_id)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(1, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 取消订单
     * @param UserModel $user
     * @return bool|mixed
     */
    public function cancel($user)
    {
        if ($this['delivery_status']['value'] == 20) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        // 订单取消事件
        return $this->transaction(function () use ($user) {
            // 回退商品库存
            (new OrderGoodsModel)->backGoodsStock($this['goods']);
            // 未付款的订单
            if ($this['pay_status']['value'] != PayStatusEnum::SUCCESS) {
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                // 回退用户积分
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
            }
            // 更新订单状态
            return $this->save(['order_status' => $this['pay_status']['value'] == PayStatusEnum::SUCCESS ? 21 : 20]);
        });
    }

    /**
     * 确认收货
     * @return bool|mixed
     */
    public function receipt()
    {
        // 验证订单是否合法
        // 条件1: 订单必须已发货
        // 条件2: 订单必须未收货
        if ($this['delivery_status']['value'] != 20 || $this['receipt_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        return $this->transaction(function () {
            // 更新订单状态
            $status = $this->save([
                'receipt_status' => 20,
                'receipt_time' => time(),
                'receipt_type' => 10,
                'order_status' => 30
            ]);
            // 获取已完成的订单
            $completed = self::detail($this['order_id'], [
                'user', 'address', 'goods', 'express',    // 用于好物圈
            ]);

            ## 增加积分、返利、出货人余额
            UserModel::doIntegralRebate($completed);

            // 已完成订单结算
            // 条件：后台订单流程设置 - 已完成订单设置0天不允许申请售后
            if (SettingModel::getItem('trade')['order']['refund_days'] == 0) {
                (new OrderCompleteService)->settled([$completed]);
            }
            // 发放分销商佣金
            DealerOrderModel::grantMoney($completed, OrderTypeEnum::MASTER);
            // 更新好物圈订单状态
            (new WowService(self::$wxapp_id))->update([$completed]);
            return $status;
        });
    }

    /**
     * 获取订单总数
     * @param $user_id
     * @param string $type
     * @return int|string
     * @throws \think\Exception
     */
    public function getCount($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = PayStatusEnum::PENDING;
                break;
            case 'received';
                $filter['pay_status'] = PayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->where('user_id', '=', $user_id)
            ->where('order_status', '<>', 20)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 订单详情
     * @param $order_id
     * @param null $user_id
     * @return null|static
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        if (!$order = self::get([
            'order_id' => $order_id,
            'user_id' => $user_id,
        ], [
            'goods' => ['image', 'sku', 'goods', 'refund'],
            'address', 'express', 'extract_shop'
        ])
        ) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 判断商品库存不足 (未付款订单)
     * @param $goodsList
     * @return bool
     */
    private function checkGoodsStatusFromOrder($goodsList)
    {
        foreach ($goodsList as $goods) {
            // 判断商品是否下架
            if (
                empty($goods['goods'])
                || $goods['goods']['goods_status']['value'] != 10
            ) {
                $this->setError("很抱歉，商品 [{$goods['goods_name']}] 已下架");
                return false;
            }
            // sku已不存在
            if (empty($goods['sku'])) {
                $this->setError("很抱歉，商品 [{$goods['goods_name']}] sku已不存在，请重新下单");
                return false;
            }
            // 付款减库存
            if ($goods['deduct_stock_type'] == 20 && $goods['total_num'] > $goods['sku']['stock_num']) {
                $this->setError("很抱歉，商品 [{$goods['goods_name']}] 库存不足");
                return false;
            }
        }
        return true;
    }

    /**
     * 判断当前订单是否允许核销
     * @param static $order
     * @return bool
     */
    public function checkExtractOrder(&$order)
    {
        if (
            $order['pay_status']['value'] == PayStatusEnum::SUCCESS
            && $order['delivery_type']['value'] == DeliveryTypeEnum::EXTRACT
            && $order['delivery_status']['value'] == 10
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    /**
     * 当前订单是否允许申请售后
     * @return bool
     */
    public function isAllowRefund()
    {
        // 允许申请售后期限
        $refund_days = SettingModel::getItem('trade')['order']['refund_days'];
        if ($refund_days == 0) {
            return false;
        }
        if (time() > $this['receipt_time'] + ((int)$refund_days * 86400)) {
            return false;
        }
        if ($this['receipt_status']['value'] != 20) {
            return false;
        }
        return true;
    }

    /**
     * 设置错误信息
     * @param $error
     */
    protected function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 获取代理销售信息
     * @param $supply_user_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentSaleInfo($supply_user_id){
        return self::where(['supply_user_id'=>$supply_user_id, 'order_status'=>30])->with(
            [
                'goods' => function(Query $query){
                    $query->field(['order_goods_id', 'order_id', 'total_num']);
                }
            ]
        )->field(['order_id', 'pay_price', 'express_price', 'order_status'])->select();
    }

}
