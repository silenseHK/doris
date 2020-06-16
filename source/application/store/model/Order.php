<?php

namespace app\store\model;

use app\common\model\Order as OrderModel;
use app\common\service\order\Refund;
use app\store\model\User as UserModel;
use app\store\model\user\BalanceLog;
use app\store\model\user\IntegralLog;
use app\store\model\UserCoupon as UserCouponModel;
use app\store\service\order\Export as Exportservice;

use app\common\library\helper;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\service\Message as MessageService;
use app\common\service\order\Refund as RefundService;
use app\common\service\wechat\wow\Order as WowService;
use think\Db;
use think\db\Query;
use think\Exception;

/**
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     * @param string $dataType
     * @param array $query
     * @param int $deliveryType
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($dataType, $query = [], $deliveryType=10)
    {
        if($deliveryType == 30){
            $this->where('delivery_type',30);
        }else{
            $this->where('delivery_type','neq','30');
        }

        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        return $this
            ->with(
                [
                    'goods.image',
                    'address',
                    'user',
                    'supplyUser.grade',
                    'supplyGrade',
                    'userGrade'
                ]
            )
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 订单列表
     * @param array $query
     * @param int $deliveryType
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getStockList($query = [], $deliveryType=10)
    {
        if($deliveryType == 30){
            $this->where('delivery_type',30);
        }else{
            $this->where('delivery_type','neq','30');
        }

        // 检索查询条件
        !empty($query) && $this->setStockWhere($query);
        // 获取数据列表
        return $this
            ->with(
                [
                    'goods.image',
                    'address',
                    'user' => function(Query $query){
                        $query->with(['grade']);
                    },
                    'supplyUser.grade',
                    'supplyGrade',
                    'userGrade'
                ]
            )
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 订单列表(全部)
     * @param $dataType
     * @param array $query
     * @param int $deliveryType
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListAll($dataType, $query = [], $deliveryType=10)
    {
        if($deliveryType == 30){
            $this->where('delivery_type',30);
        }else{
            $this->where('delivery_type','neq','30');
        }

        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        return $this->with(['goods.image', 'address', 'user', 'extract_shop'])
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->select();
    }

    /**
     * 订单导出
     * @param $dataType
     * @param $query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        // 导出csv文件
        return (new Exportservice)->orderList($list);
    }

    /**
     * 订单导出
     * @param $dataType
     * @param $query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList2($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        // 导出csv文件
        return (new Exportservice)->orderList2($list);
    }

    /**
     * 批量发货模板
     */
    public function deliveryTpl()
    {
        return (new Exportservice)->deliveryTpl();
    }

    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['search']) && !empty($query['search'])) {
            $this->where('order_no|user.nickName', 'like', '%' . trim($query['search']) . '%');
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $this->where('order.create_time', '>=', strtotime($query['start_time']));
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $this->where('order.create_time', '<', strtotime($query['end_time']) + 86400);
        }
        if (isset($query['delivery_type']) && !empty($query['delivery_type'])) {
            $query['delivery_type'] > -1 && $this->where('delivery_type', '=', $query['delivery_type']);
        }
        if (isset($query['extract_shop_id']) && !empty($query['extract_shop_id'])) {
            $query['extract_shop_id'] > -1 && $this->where('extract_shop_id', '=', $query['extract_shop_id']);
        }
        // 用户id
        if (isset($query['user_id']) && $query['user_id'] > 0) {
            $this->where('order.user_id', '=', (int)$query['user_id']);
        }
    }

    public function setStockWhere($query){
        if(isset($query['order_status']) && !empty($query['order_status'])){
            $query['order_status'] > 0 && $this->where(['pay_status'=>$query['order_status']]);
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $this->where('order.create_time', '>=', strtotime($query['start_time']));
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $this->where('order.create_time', '<', strtotime($query['end_time']) + 86400);
        }
        if(isset($query['search']) && !empty($query['search'])){
            $keywords = str_filter($query['search']);
            $search_type = intval($query['search_type']);
            switch($search_type){
                case 10: #订单号
                    $this->where(['order_no'=>['LIKE', "%{$keywords}%"]]);
                    break;
                case 20: #发货人
                    ##先查找user_id
                    $like_user_ids = UserModel::getLikeUserByName($keywords);
                    $this->where(['supply_user_id'=>['IN', $like_user_ids]]);
                    break;
                case 30: #进货人
                    ##先查user_id
                    $like_user_ids = UserModel::getLikeUserByName($keywords);
                    $this->where(['user_id'=>['IN', $like_user_ids]]);
                    break;
            }
        }
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private function transferDataType($dataType)
    {
        // 数据类型
        $filter = [];
        switch ($dataType) {
            case 'delivery': ##待发货
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 10,
                    'order_status' => ['in', [10, 21]]
                ];
                break;
            case 'receipt': ##待收货
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 20,
                    'receipt_status' => 10
                ];
                break;
            case 'pay': ##待支付
                $filter = ['pay_status' => 10, 'order_status' => 10];
                break;
            case 'complete': ##已完成
                $filter = ['order_status' => 30];
                break;
            case 'cancel': ##已取消
                $filter = ['order_status' => 20];
                break;
            case 'all': ##全部
                $filter = [];
                break;
        }
        return $filter;
    }

    /**
     * 确认发货(单独订单)
     * @param $data
     * @return array|bool|false
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function delivery($data)
    {
        // 转义为订单列表
        $orderList = [$this];
        // 验证订单是否满足发货条件
        if (!$this->verifyDelivery($orderList)) {
            return false;
        }
        // 整理更新的数据
        $updateList = [[
            'order_id' => $this['order_id'],
            'express_id' => $data['express_id'],
            'express_no' => $data['express_no']
        ]];
        // 更新订单发货状态
        if ($status = $this->updateToDelivery($updateList)) {
            // 获取已发货的订单
            $completed = self::detail($this['order_id'], ['user', 'address', 'goods', 'express']);
            // 发送消息通知
            $this->sendDeliveryMessage([$completed]);
            // 同步好物圈订单
            (new WowService($this['wxapp_id']))->update([$completed]);
        }
        return $status;
    }

    /**
     * 批量发货
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function batchDelivery($data)
    {
        // 获取csv文件中的数据
        if (!$csvData = $this->getCsvData()) {
            return false;
        }
        // 整理订单id集
        $orderNos = helper::getArrayColumn($csvData, 0);
        // 获取订单列表数据
        $orderList = helper::arrayColumn2Key($this->getListByOrderNos($orderNos), 'order_no');
        // 验证订单是否存在
        $tempArr = array_values(array_diff($orderNos, array_keys($orderList)));
        if (!empty($tempArr)) {
            $this->error = "订单号[{$tempArr[0]}] 不存在!";
            return false;
        }
        // 整理物流单号
        $updateList = [];
        foreach ($csvData as $item) {
            $updateList[] = [
                'order_id' => $orderList[$item[0]]['order_id'],
                'express_id' => $data['express_id'],
                'express_no' => $item[1],
            ];
        }
        // 验证订单是否满足发货条件
        if (!$this->verifyDelivery($orderList)) {
            return false;
        }
        // 更新订单发货状态(批量)
        if ($status = $this->updateToDelivery($updateList)) {
            // 获取已发货的订单
            $completed = $this->getListByOrderNos($orderNos, ['user', 'address', 'goods', 'express']);
            // 发送消息通知
            $this->sendDeliveryMessage($completed);
            //  同步好物圈订单
            (new WowService(self::$wxapp_id))->update($completed);
        }
        return $status;
    }

    /**
     * 确认发货后发送消息通知
     * @param array|\think\Collection $orderList
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function sendDeliveryMessage($orderList)
    {
        // 实例化消息通知服务类
        $Service = new MessageService;
        foreach ($orderList as $item) {
            // 发送消息通知
            $Service->delivery($item, OrderTypeEnum::MASTER);
        }
        return true;
    }

    /**
     * 更新订单发货状态(批量)
     * @param $orderList
     * @return array|false
     * @throws \Exception
     */
    private function updateToDelivery($orderList)
    {
        $data = [];
        foreach ($orderList as $item) {
            $data[] = [
                'order_id' => $item['order_id'],
                'express_no' => $item['express_no'],
                'express_id' => $item['express_id'],
                'delivery_status' => 20,
                'delivery_time' => time(),
            ];
        }
        return $this->isUpdate()->saveAll($data);
    }

    /**
     * 验证订单是否满足发货条件
     * @param $orderList
     * @return bool
     */
    private function verifyDelivery($orderList)
    {
        foreach ($orderList as $order) {
            if (
                $order['pay_status']['value'] != 20
                || $order['delivery_type']['value'] != DeliveryTypeEnum::EXPRESS
                || $order['delivery_status']['value'] != 10
            ) {
                $this->error = "订单号[{$order['order_no']}] 不满足发货条件!";
                return false;
            }
        }
        return true;
    }

    /**
     * 获取csv文件中的数据
     * @return array|bool
     */
    private function getCsvData()
    {
        // 获取表单上传文件 例如上传了001.jpg
        if (!$file = \request()->file('iFile')) {
            $this->error = '请上传发货模板';
            return false;
        }
        // 设置区域信息
        setlocale(LC_ALL, 'zh_CN');
        // 打开上传的文件
        $csvFile = fopen($file->getInfo()['tmp_name'], 'r');
        // 忽略第一行(csv标题)
        fgetcsv($csvFile);
        // 遍历并记录订单信息
        $orderList = [];
        while ($item = fgetcsv($csvFile)) {
            if (!isset($item[0]) || empty($item[0]) || !isset($item[1]) || empty($item[1])) {
                $this->error = '模板文件数据不合法';
                return false;
            }
            $orderList[] = $item;
        }
        if (empty($orderList)) {
            $this->error = '模板文件中没有订单数据';
            return false;
        }
        return $orderList;
    }

    /**
     * 修改订单价格
     * @param $data
     * @return bool
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'order_price' => $data['update_price'],
                'pay_price' => $payPrice,
                'update_price' => helper::bcsub($data['update_price'], helper::bcsub($this['total_price'], $this['coupon_money'])),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }

    /**
     * 审核：用户取消订单
     * @param $data
     * @return bool|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function confirmCancel($data)
    {
        // 判断订单是否有效
        if ($this['pay_status']['value'] != 20) {
            $this->error = '该订单不合法';
            return false;
        }
        // 订单取消事件
        $status = $this->transaction(function () use ($data) {
            if ($data['is_cancel'] == true) {
                // 执行退款操作
                (new RefundService)->execute($this);
                // 回退商品库存
                $res = (new OrderGoods)->backGoodsStock($this, true);
                if($res !== true)throw new Exception($res);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                // 回退用户积分
                $User = UserModel::detail($this['user_id']);
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $User->setIncPoints($this['points_num'], $describe);
            }
            // 更新订单状态
            return $this->save(['order_status' => $data['is_cancel'] ? 20 : 10]);
        });
        if ($status == true) {
            // 同步好物圈订单
            (new WowService(self::$wxapp_id))->update([$this]);
        }
        return $status;
    }

    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getPayOrderTotal($day = null)
    {
        $filter = [
            'pay_status' => 20,
            'order_status' => ['<>', 20],
        ];
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $filter['pay_time'] = [
                ['>=', $startTime],
                ['<', $startTime + 86400],
            ];
        }
        return $this->getOrderTotal($filter);
    }

    /**
     * 获取订单总数量
     * @param array $filter
     * @return int|string
     * @throws \think\Exception
     */
    public function getOrderTotal($filter = [])
    {
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 获取某天的总销售额
     * @param $day
     * @return float|int
     */
    public function getOrderTotalPrice($day)
    {
        $startTime = strtotime($day);
        return $this->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->sum('pay_price');
    }

    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $userIds = $this->distinct(true)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('is_delete', '=', 0)
            ->column('user_id');
        return count($userIds);
    }

    public function warehouse(){
        $goods_id = 21;
        ##库存
        $stock_info = GoodsSku::where(['goods_id'=>$goods_id])->field(['stock_num', 'total_stock_num', 'goods_sku_id'])->find();
        ##发货量
        $deliver_info = OrderGoods::getDeliverInfo($stock_info['goods_sku_id']);
        ##云库存
        $cloud_stock = UserGoodsStock::getCloudStock($stock_info['goods_sku_id']);
        ##出货量[平台出货]
        $ship_info = OrderGoods::getShipInfo($stock_info['goods_sku_id']);
        $nums = compact('stock_info','cloud_stock','deliver_info','ship_info');

        ##时间变化的量
        $time_nums = $this->getTimeNums($goods_id);

        ##库存
        $spec_list = GoodsSku::getGoodsSpecList();

        ##待发货
        $deliver_list = OrderGoods::getDeliverList();

        return compact('nums','time_nums','spec_list','deliver_list');
    }

    public function getTimeNums($goods_id=21){
        $goods_sku_id = GoodsSku::where(['goods_id'=>$goods_id])->value('goods_sku_id');
        $start_time = input('start_time','','str_filter');
        $end_time = input('end_time','','str_filter');
        if($start_time && $end_time){
            $start_time = strtotime($start_time . " 00:00:01");
            $end_time = strtotime($end_time . " 23:59:59");
        }else{
            $start_time = $end_time = 0;
        }
        ##待发货量
        $wait_deliver = OrderGoods::getWaitDeliverInfo($goods_sku_id, $start_time, $end_time);
        ##待提货量
        $wait_take = OrderGoods::getWaitTakeInfo($goods_sku_id, $start_time, $end_time);
        ##已发货量
        $wait_receipt = OrderGoods::getWaitReceiptInfo($goods_sku_id, $start_time, $end_time);
        ##已完成量
        $complete = OrderGoods::getCompleteInfo($goods_sku_id, $start_time, $end_time);
        return compact('wait_deliver','wait_take','wait_receipt','complete');
    }

    /**
     * 退款
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function refund(){
        ##参数
        $order_id = input('post.order_id',0,'intval');
        $remark = input('post.remark','','str_filter');
        ##操作
        $order_info = self::get(['order_id'=>$order_id], ['goods', 'user', 'supplyUser']);
//        if(in_array($order_info['order_status']['value'], [20, 21, 40]) || $order_info['pay_status']['value'] != 20)throw new Exception('该订单不支持此操作');
//        print_r($order_info->toArray());die;
        $supply_refund_money = $rebate_refund_money = 0;
        if($order_info['order_status']['value'] == 30){ ##已完成订单
            ##检查订单
            if($order_info['supply_user_id'] > 0){
                ##查看出货人余额
                $supply_refund_money = $order_info['order_price'] - $order_info['rebate_money'];
                if($order_info['supply_user']['balance'] < $supply_refund_money)throw new Exception('出货人余额不足');
            }
            if($order_info['rebate_money'] > 0){
                $rebate_refund_money = $order_info['rebate_money'];
                ##检查返利用户余额
                foreach($order_info['rebate_info'] as $item){
                    if($item['money'] > User::getUserBalance($item['user_id']))throw new Exception('返利用户余额不足');
                }
            }
        }
        $refund_money = $order_info['pay_price'];
        if($order_info['pay_status']['value'] == 20)$refund_money = $order_info['order_price'];

        ##退款
        $refund_data = [
            'order_id' => $order_id,
            'order_goods_id' => $order_info['goods'][0]['goods_id'],
            'refund_money' => $refund_money,
            'remark' => $remark,
            'order_status' => $order_info['order_status']['value']
        ];
        Db::startTrans();
        try{
            ##恢复库存
            $flag = $order_info['order_status']['value'] == 30 ? 3 : 2;
            if($order_info['supply_user_id'] > 0){
                UserGoodsStock::refundStock($order_info['supply_user_id'], $order_info['goods'][0]['goods_id'], $order_info['goods'][0]['goods_sku_id'], $order_info['goods'][0]['total_num'], $flag, $order_info['order_no']);
            }else{
                Goods::refund($order_info['goods'][0]);
            }
            ##扣除进货的库存
            if($order_info['delivery_type']['value'] == 30){
                UserGoodsStock::rebackStock($order_info['user_id'], $order_info['goods'][0]['goods_id'], $order_info['goods'][0]['goods_sku_id'], $order_info['goods'][0]['total_num'], $order_info['order_no']);
            }
            ##改变订单状态为已退款
            $res = self::where(['order_id'=>$order_id])->setField('order_status',40);
            if($res === false)throw new Exception('操作失败');
            ##返还积分和等级、返还返利金额、
            if($order_info['order_status']['value'] == 30){
                $integral_info = IntegralLog::get(['order_id'=>$order_id]);
                if($integral_info){
                    IntegralLog::refund($integral_info, $order_id);
                }
            }
            ##返还货款
            if($supply_refund_money > 0){
                BalanceLog::refund($order_info['supply_user_id'], $supply_refund_money, $order_id,1);
            }
            ##返还返利金额
            if($rebate_refund_money > 0){
                foreach($order_info['rebate_info'] as $item){
                    BalanceLog::refund($item['user_id'], $item['money'], $order_id,2);
                }
            }
            ##更新订单状态
            self::where(['order_id'=>$order_id])->setField('order_status',40);
            ##增加退款记录
            OrderRefundLog::add($refund_data);
            ##退款
            $refund = new Refund();
            $res = $refund->execute($order_info, $refund_money);
            if(!$res)throw new Exception('退款失败');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getUserOrderList(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $goods_sku_id = input('post.goods_sku_id',0,'intval');
        $where = [
            'o.pay_status' => 20,
            'og.goods_sku_id' => $goods_sku_id
        ];
        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where['o.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        ##数据
        $list = $this->alias('o')
            ->join('order_goods og','o.order_id = og.order_id','LEFT')
            ->where($where)
            ->where(function(Query $query) use ($user_id){
                $query->where(['o.user_id'=>$user_id])->whereOr(['o.supply_user_id'=>$user_id])->whereOr(['o.rebate_user_id'=> ['LIKE', "%[{$user_id}]%"]]);
            })
            ->with(
                [
                    'stockLog',
                    'balanceLog',
                    'supplyUser',
                    'user',
                    'supplyGrade',
                    'userGrade',
                    'goods'
                ]
            )
            ->order('o.create_time','desc')
            ->paginate(15,false,[
                'type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:ajax_order_go([PAGE]);'
            ]);
        $page = $list->render();
//        print_r($list->toArray());die;
        return compact('page','list');
    }

    /**
     * 用户微信支付的订单
     * @param $user_id
     * @param $start_time
     * @param $end_time
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserWxPayOrderList(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $where = [
            'user_id'=>$user_id,
            'order_status' => ['IN', [10, 30]],
            'pay_status' => 20,
            'pay_type' => ['IN', [20, 30]]
        ];
         if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
         }
         $list = $this
             ->where($where)
             ->with(
                [
                    'stockLog',
                    'supplyUser',
                    'user',
                    'supplyGrade',
                    'userGrade',
                    'goods' => function(Query $query){
                        $query->with(['sku.image']);
                    }
                ]
             )
             ->order('create_time','desc')
             ->paginate(10,false,[
                 'type' => 'Bootstrap',
                 'var_page' => 'page',
                 'path' => 'javascript:ajax_wxpay_go([PAGE]);'
             ]);

        $page = $list->render();
        return compact('page','list');
    }

    /**
     * 获取运费信息
     * @return array
     * @throws \think\exception\DbException
     */
    public function getOrderFreight(){
        ##参数
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $where = [
            'order_status' => ['IN', [10, 30, 40]],
            'delivery_status' => 20,
            'pay_status' => 20,
            'delivery_type' => 10,
            'express_price' => ['GT', 0]
        ];
        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $list = $this
            ->with(
                [
                    'goods.spec.image',
                    'user',
                    'address'
                ]
            )
            ->where($where)
            ->order('create_time','desc')
            ->paginate(10,false,[
                'type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:ajax_order_freight_go([PAGE]);'
            ]);
        ##总运费
        $total_freight = 0;
        if(!$list->isEmpty())$total_freight = $this->where($where)->sum('express_price');
        $page = $list->render();
        return compact('page','list','total_freight');
    }

}
