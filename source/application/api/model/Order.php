<?php

namespace app\api\model;

use app\api\model\user\BalanceLog;
use app\api\service\order\PaySuccess;
use app\api\validate\order\Checkout;
use app\api\validate\user\OrderValidate;
use app\common\model\GoodsGrade;
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

use app\common\model\user\Grade;
use app\common\model\UserGoodsStock;
use app\common\service\wechat\wow\Order as WowService;
use app\common\service\order\Complete as OrderCompleteService;
use app\common\exception\BaseException;
use think\db\Query;
use think\Exception;

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
        return self::get(['out_trade_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0], ['goods', 'user']);
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
        $this->updateSupplyUser2();
        // 判断商品状态、库存
        if (!$this->checkGoodsStatusFromOrder($this)) {
            return false;
        }
        // 余额支付
        if ($payType == PayTypeEnum::BALANCE) {
            return $this->onPaymentByBalance($this['out_trade_no']);
        }
        return true;
    }

    /**
     * 更新订单（出货人 价格 支付金额 out_trade_no）
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateSupplyUser(){
        $goodsList = $this['goods'];
        $agentGoods = [];
        foreach($goodsList as $goods){
            if($goods['sale_type'] == 1){
                if(!isset($agentGoods[$goods['goods_id']]))$agentGoods[$goods['goods_id']] = 0;
                $agentGoods[$goods['goods_id']] += $goods['total_num'];
            }
        }
        if(!empty($agentGoods)){
            ##获取最新的供货人
            $agentData= User::repayGetSupplyGoodsUser($this['user_id'], $agentGoods);
            if($this['supply_user_id'] != $agentData['supplyUserId']){
                ##更新出货人
                $this->where(['order_id'=>$this['order_id']])->update(
                    [
                        'supply_user_id'=>$agentData['supplyUserId'],
                        'supply_user_grade_id'=>$agentData['supplyUserGradeId'],
                        'user_grade_id' => $agentData['grade_id']
                    ]
                );
            }
            ##更新规格价格
            if(!empty($agentData['goodsData'])){
                $goodsData = $agentData['goodsData'];
                foreach($goodsList as $k => $v){
                    if(isset($goodsData[$v['goods_id']]) && $goodsData[$v['goods_id']] != $v['goods_price']){
                        ##更新价格[order_goods]
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
        $this['out_trade_no'] = $this->orderNo();
        $this->where(['order_id'=>$this['order_id']])->update(['out_trade_no'=>$this['out_trade_no'], 'pay_price'=>$this['pay_price'], 'total_price'=>$payPrice, 'order_price'=>$payPrice]);
        return true;
    }

    public function updateSupplyUser2(){
        $goodsList = $this['goods'];
        $agentGoods = [];
        foreach($goodsList as $goods){
            if($goods['sale_type'] == 1){
                if(!isset($agentGoods[$goods['goods_id']]))$agentGoods[$goods['goods_id']] = 0;
                $agentGoods[$goods['goods_id']] += $goods['total_num'];
            }
        }

        $user = User::get(['user_id'=> $this['user_id']], ['grade']);
        $agentGoodsPrice = [];
        $is_achievement = 10;
        foreach($agentGoods as $goods_id => $num){
            ##获取上级供应商
            $grade_info = \app\common\model\User::getBuyGoodsGrade2($goods_id, $num);
            if($user['grade']['weight'] >= $grade_info['weight']){
                $grade_weight = $user['grade']['weight'];
                $grade_id = $user['grade_id'];
                if($grade_weight > 10){
                    $is_achievement = 20;
                }
            }else{
                $grade_weight =$grade_info['weight'];
                $grade_id = $grade_info['grade_id'];
                if($grade_weight > 10){
                    $is_achievement = 30;
                }
            }
            $agentGoodsPrice[$goods_id] = GoodsGrade::getGoodsPrice($grade_id, $goods_id);
        }

        if(!empty($agentGoods)){
            ##获取最新的供货人
            $applyGradeIds = Grade::getApplyGrade2($grade_weight);
            if(empty($applyGradeIds)){
                $supplyUserId = 0;
            }else{
                $relation = $user['relation'];
                $relation = explode('-', $relation);
                ##获取供应人id
                $relation_ids = implode(',', $relation);
                $relation_ids = trim($relation_ids,',');
                $user_id = User::where(['user_id'=>['IN', $relation], 'grade_id'=>['IN', $applyGradeIds], 'is_delete'=>0, 'status'=>1])->orderRaw("field(user_id," . $relation_ids . ")")->value('user_id');
                $supplyUserId = $user_id ? : 0;
            }
            $supply_user_grade_id = $supplyUserId?UserModel::getUserGrade($supplyUserId):0;

            ##更新返利 + 发货人
            $rebateUser = \app\common\model\User::getcaseRebate($this['user_id'], $goods_id, $agentGoods[$goods_id], $supplyUserId);
            if(!empty($rebateUser)){
                $rebateMoney = self::sumRebate($rebateUser);
                $rebateUsers = self::combineRebateUser($rebateUser);
            }
            $updateData['rebate_user_id'] = isset($rebateUsers) ? $rebateUsers : "";
            $updateData['rebate_money'] = isset($rebateMoney)? $rebateMoney : 0;
            $updateData['rebate_info'] = !empty($rebateUser)? json_encode($rebateUser) : "";
            $updateData['supply_user_id'] = $supplyUserId;
            $updateData['supply_user_grade_id'] = $supply_user_grade_id;
            $updateData['user_grade_id'] = $grade_id;
//            $agentData= User::repayGetSupplyGoodsUser($this['user_id'], $agentGoods);
            $this->where(['order_id'=>$this['order_id']])->update($updateData);
        }

        ##更新规格价格
        if(!empty($agentGoodsPrice)){
            foreach($goodsList as $k => $v){
                if(isset($agentGoodsPrice[$v['goods_id']]) && $agentGoodsPrice[$v['goods_id']] != $v['goods_price']){
                    ##更新价格[order_goods]
                    OrderGoods::editPrice($v['order_goods_id'], $agentGoodsPrice[$v['goods_id']], $agentGoods[$v['goods_id']]);
                    $this['goods'][$k]['total_price'] = $agentGoodsPrice[$v['goods_id']] * $agentGoods[$v['goods_id']];
                }
            }
        }

        if($is_achievement != $this['is_achievement']){
            ##更新业绩增加类型
            $this->where(['order_id' => $this['order_id']])->update(
                [
                    'is_achievement' => $is_achievement
                ]
            );
        }

        ##计算最新价格
        $payPrice = 0;
        foreach($this['goods'] as $v){
            $payPrice += $v['total_price'];
        }
        $this['pay_price'] = $payPrice + $this['express_price'];
        $this['out_trade_no'] = $this->orderNo();
        $this->where(['order_id'=>$this['order_id']])->update(['out_trade_no'=>$this['out_trade_no'], 'pay_price'=>$this['pay_price'], 'total_price'=>$payPrice, 'order_price'=>$payPrice]);
        return true;
    }

    /**
     * 计算返利金额
     * @param $rebateUser
     * @return float|int
     */
    public static function sumRebate($rebateUser){
        return array_sum(array_column($rebateUser, 'money'));
    }

    /**
     * 生成获利人
     * @param $rebateUser
     * @return string
     */
    public static function combineRebateUser($rebateUser){
        $users = array_column($rebateUser, 'user_id');
        $rtn = "";
        foreach($users as $v){
            $rtn .= "[{$v}]";
        }
        return $rtn;
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
            $order['out_trade_no'],
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
                $item['goods_price'] = UserModel::getAgentGoodsPrice2($user['grade'],$goodsId,$goodsNum);
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
        $size = input('get.size',6,'intval');
        return $this->with(['goods.image'])
            ->where('user_id', '=', $user_id)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($size, false, [
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
            // 未付款的订单
            if ($this['pay_status']['value'] != PayStatusEnum::SUCCESS) {
                // 回退商品库存
                //(new OrderGoodsModel)->backGoodsStock($this['goods']);
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
     * @param $order
     * @return bool
     */
    private function checkGoodsStatusFromOrder($order)
    {
        $goodsList = $order['goods'];
        foreach ($goodsList as $goods) {
            // 判断商品是否下架
            if(
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
            // 付款减库存&&平台发货
            if($goods['deduct_stock_type'] == 20 && $order['supply_user_id'] == 0 && $goods['total_num'] > $goods['sku']['stock_num']){
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

    /**
     * 代理出货列表
     * @param $user_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentSaleGoodsLists($user_id){
        $validate = new Checkout();
        if(!$validate->scene('agent_sale_order')->check(input()))throw new Exception($validate->getError());
        ##参数
        $order_type = input('get.order_type',0,'intval');
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        $this->setAgentOrderWhere($order_type, $user_id);
        ##订单列表
        $list = $this
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'mobile']);
                    },
                    'goods' => function(Query $query){
                        $query
                            ->field(['order_id', 'goods_sku_id', 'goods_name', 'total_num', 'goods_price', 'sale_type'])
                            ->with(
                                [
                                    'spec'=>function(Query $query){
                                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with(['image']);
                                    }
                                ]
                            );
                    }
                ]
            )
            ->field(['order_id', 'order_no', 'create_time', 'user_id', 'order_price', 'pay_price', 'express_price', 'delivery_type', 'order_status', 'pay_status', 'delivery_status', 'receipt_status'])
            ->page($page, $size)
            ->select();
        return $list;
    }

    /**
     * 设置代理出货筛选条件
     * @param $order_type
     * @param $user_id
     */
    public function setAgentOrderWhere($order_type, $user_id){
        $where = [
            'supply_user_id' => $user_id,
            'pay_status' => 20,
            'order_status' => ['IN', [10, 30]]
        ];
        switch($order_type){
            case 10: ##待发货
                $where['delivery_status'] = 10;
                $where['order_status'] = 10;
                break;
            case 20: ##待收货
                $where['delivery_status'] = 20;
                $where['receipt_status'] = 10;
                break;
            case 30: ##已完成
//                $where['receipt_status'] = 20;
                $where['order_status'] = 30;
                break;
        }
        $this->where($where);
    }

    /**
     * 获取待入账金额
     * @param $user_id
     * @return float|int
     */
    public static function getUserWaitIncomeMoney($user_id){
        $model = new self;
        #待入账= 出货待入账 + 体验装返利待入账
        ##出货待入账
        $supply_money = $model->where(
                [
                    'supply_user_id' => $user_id,
                    'pay_status' => 20,
                    'delivery_type' => ['IN', [10, 20]],
                    'receipt_status' => 10,
                    'order_status' => 10
                ]
            )
            ->sum('order_price');
        ##体验装返利待入账
        $experience_list = $model->where(['rebate_user_id'=>['LIKE', "%[$user_id]%"], 'delivery_type'=>['IN', [10, 20]], 'pay_status'=>20, 'order_status'=>10])->field(['rebate_info'])->select();
        $experience_money = 0;
        if(!$experience_list->isEmpty()){
            foreach($experience_list as $item){
                foreach($item['rebate_info'] as $it){
                    if($it['user_id'] == $user_id){
                        $experience_money += $it['money'];
                        continue;
                    }
                }
            }
        }

        $money = $supply_money + $experience_money;
        return $money;
    }

    /**
     * 获取收入列表
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getIncomeList($user){
        ##参数
        $validate = new OrderValidate();
        if(!$validate->scene('income_list')->check(input()))throw new Exception($validate->getError());
        ##参数
        $params = [
            'type' => input('get.type',10,'intval'),
            'keywords' => input('get.keywords','','keywords_filter'),
            'start_time' => input('get.start_time','','str_filter'),
            'end_time' => input('get.end_time','','str_filter')
        ];

        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        ##设置筛选条件
        $this->setIncomeListWhere($params, $user);

        $list = $this
            ->field(['order_id', 'order_no', 'user_id', 'supply_user_id', 'order_price', 'delivery_type', 'rebate_info', 'rebate_money', 'order_status', 'create_time', 'pay_status', 'delivery_status', 'receipt_status'])
            ->with(
                [
                    'goods' => function(Query $query){
                        $query
                            ->field(['goods_id', 'order_id', 'goods_name', 'goods_sku_id', 'spec_sku_id', 'goods_price', 'total_num'])
                            ->with(
                                [
                                    'spec' => function(Query $query){
                                        $query->field(['goods_sku_id', 'spec_sku_id', 'goods_id', 'image_id'])->with(['image'=>function(Query $query){$query->field(['file_id', 'storage', 'file_name']);}]);
                                    }
                                ]
                            );
                    },
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'mobile']);
                    },
                    'supplyUser' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'mobile']);
                    }
                ]
            )
            ->page($page, $size)
            ->order('create_time','desc')
            ->select();

        ##计算收入
        if($params['type'] == 10){ ##货款 - 返利
            foreach($list as &$item){
                $item['income'] = $item['order_price'] - $item['rebate_money'];
            }
        }else{ ##返利
            foreach($list as &$item){
                foreach($item['rebate_info'] as $rebate){
                    if($rebate['user_id'] == $user['user_id']){
                        $item['income'] = $rebate['money'];
                        break;
                    }
                }
            }
        }

        ##总收入
        $total_income = $this->getTotalIncome($params, $user['user_id']);

        return compact('list','total_income');
    }

    /**
     * 设置收入列表筛选条件
     * @param $params
     * @param $user
     */
    public function setIncomeListWhere($params, $user){
        if($params['type'] == 10){ ##货款收入
            $where = [
                'supply_user_id' => $user['user_id']
            ];
            $where['order_status'] = 30;
        }else{ ##返利佣金
            $where = [
                'rebate_user_id' => ['LIKE', "%[{$user['user_id']}]%"]
            ];
            $where['pay_status'] = 20;
            $where['order_status'] = ['IN', [10, 30]];
        }
        ## 订单号
        if($params['keywords']){
            $where['order_no'] = $params['keywords'];
        }
        ## 时间筛选
        if($params['start_time'] && $params['end_time']){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $end_time = strtotime($params['end_time'] . " 23:59:59");
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }

        $this->where($where);
    }

    /**
     * 获取总收入
     * @param $params
     * @param $user_id
     * @return float|int
     */
    public function getTotalIncome($params, $user_id){
        ## 时间筛选
        $start_time = $end_time = $order_id = 0;
        if($params['start_time'] && $params['end_time']){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $end_time = strtotime($params['end_time'] . " 23:59:59");
        }
        ## 订单号
        if($params['keywords']){
            $order_id = $this->where(['order_no'=>$params['keywords']])->value('order_id');
            if(!$order_id)return 0;
        }
        ##类型
        $scene = $params['type'] == 10 ? 50 : 60;
        ##计算收入
        $income = BalanceLog::getIncome($start_time, $end_time, $order_id, $user_id, $scene);
        return $income;
    }

}
