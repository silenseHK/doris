<?php

namespace app\store\model;

use app\api\service\order\PaySuccess;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\user\balanceLog\Scene;
use app\common\enum\user\grade\GradeSize;
use app\common\enum\user\grade\GradeType;
use app\common\model\GoodsStockLog;
use app\common\model\NoticeMessage;
use app\common\model\RebateLog;
use app\common\model\User as UserModel;

use app\common\model\UserGoodsStock;
use app\store\model\user\BalanceLog;
use app\store\model\user\Grade;
use app\store\model\user\GradeLog as GradeLogModel;
use app\store\model\user\BalanceLog as BalanceLogModel;
use app\store\model\user\IntegralLog;
use app\store\model\user\PointsLog as PointsLogModel;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use app\common\library\helper;
use app\store\validate\UserValid;
use think\Db;
use think\db\Query;
use think\Exception;
use think\Hook;

/**
 * 用户模型
 * Class User
 * @package app\store\model
 */
class User extends UserModel
{

    protected $append = [
        'mobile_hide',
        'invitation_user'
    ];

    /**
     * 获取当前用户总数
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getUserTotal($day = null)
    {
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $this->where('create_time', '>=', $startTime)
                ->where('create_time', '<', $startTime + 86400);
        }
        return $this->where('is_delete', '=', '0')->count();
    }

    /**
     * 获取用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param int $user_id 用户id
     * @param string $mobile 手机号
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($nickName = '', $gender = -1, $grade = null, $start_time = '', $end_time = '', $user_id = null, $mobile = '')
    {
        // 检索：微信昵称
        !empty($nickName) && $this->where('nickName', 'like', "%$nickName%");
        // 检索：性别
        if ($gender !== '' && $gender > -1) {
            $this->where('gender', '=', (int)$gender);
        }
        // 检索：会员等级
        $grade > 0 && $this->where('grade_id', '=', (int)$grade);
        // 检索：创建时间
        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $this->where('create_time','BETWEEN', [$start_time, $end_time]);
        }
        //检索：用户id
        $user_id > 0 && $this->where('user_id','=', (int)$user_id);

        //检索：手机号
        $mobile && $this->where('mobile','LIKE',"%{$mobile}%");

        // 获取用户列表
        return $this->with(['grade'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    public function getMobileHideAttr($value, $data){
        if(!isset($data['mobile']) || !$data['mobile'])return '--';
        return mobile_hide($data['mobile']);
    }

    public function getInvitationUserAttr($value, $data){
        if(!isset($data['invitation_user_id']) || !$data['invitation_user_id'])return [];
        return self::getUserInfo($data['invitation_user_id']);
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 用户充值
     * @param string $storeUserName 当前操作人用户名
     * @param int $source 充值类型
     * @param array $data post数据
     * @return bool
     */
    public function recharge($storeUserName, $source, $data)
    {
        if ($source == 0) { ## 余额充值
            return $this->rechargeToBalance($storeUserName, $data['balance']);
        } elseif ($source == 1) {  ## 库存充值
            try{
                $res = $this->rechargeToPoints2($data['points']);
                if($res !== true)throw new Exception($res);
                return true;
            }catch(Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        } elseif($source == 2){ ## 活动库存充值[可指定库存充值到指定的等级]
            try{
                $res = $this->rechargeToGrade($data['grade']);
                if($res !== true)throw new Exception($this->getError());
                return true;
            }catch(Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
        return false;
    }

    /**
     * 用户充值：余额
     * @param $storeUserName
     * @param $data
     * @return bool
     */
    private function rechargeToBalance($storeUserName, $data)
    {
        if (!isset($data['money']) || $data['money'] === '' || $data['money'] < 0) {
            $this->error = '请输入正确的金额';
            return false;
        }
        // 判断充值方式，计算最终金额
        if ($data['mode'] === 'inc') {
            $diffMoney = $data['money'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = -$data['money'];
        } else {
            $diffMoney = helper::bcsub($data['money'], $this['balance']);
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户余额
            $this->setInc('balance', $diffMoney);
            // 新增余额变动记录
            BalanceLogModel::add(SceneEnum::ADMIN, [
                'user_id' => $this['user_id'],
                'money' => $diffMoney,
                'remark' => $data['remark'],
            ], [$storeUserName]);
        });
        return true;
    }

    /**
     * 用户充值：积分
     * @param $data
     * @return bool
     */
    private function rechargeToPoints($data)
    {
        if (!isset($data['value']) || $data['value'] === '') {
            $this->error = '请输入正确的库存数量';
            return false;
        }
        if($data['mode'] !== 'final' && $data['value'] < 0){
            $this->error = '请输入正确的库存数量';
            return false;
        }

        $goodsId = intval($data['goods_id']);
        $goodsSkuId = intval($data['goods_sku_id']);
        if(!$goodsSkuId || $goodsSkuId < 0){
            $this->error = '请选择代理商品';
            return false;
        }
        ##获取当前库存
        $oldStock = UserGoodsStock::getUserGoodsStock($this['user_id'], $goodsSkuId);

        // 判断充值方式，计算最终库存
        if ($data['mode'] === 'inc') {
            $diffStock = $data['value'];
        } elseif ($data['mode'] === 'dec') {
            $diffStock = -$data['value'];
        } else {
            $diffStock = $data['value'] - $oldStock;
        }
        $finalStock = $oldStock + $diffStock;

        ##如果是增加用户库存则判断平台商品库存是否充足
        if($diffStock > 0){
            $goodsStock = Goods::getAgentGoodsStock($goodsSkuId);
            if(!$goodsStock || $goodsStock < $diffStock){
                $this->error = "商品库存不足,请补充商品库存后再充值";
                return false;
            }
        }

        $change_direction = $diffStock > 0 ? 10 : 20;
        GoodsStockLog::addLog($goodsSkuId,40, $change_direction, abs($diffStock),'',$this['user_id']);

        ##变更商品库存
        if($diffStock > 0){
            Goods::decAgentGoodsStock($goodsSkuId, $diffStock);
        }else{
            Goods::incAgentGoodsStock($goodsSkuId, abs($diffStock));
        }

        ## 变更用户商品库存
        $res = UserGoodsStock::updateUserGoodsStock($this['user_id'], $goodsId, $goodsSkuId, $finalStock, $diffStock ,'ADMIN', $data['remark']);
        if($res !== true){
            $this->error = $res;
            return false;
        }
        return true;
    }

    /**
     * 线下补充库存
     * @param $data
     * @return bool|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function rechargeToPoints2($data){
        $goods_sku_id = intval($data['goods_sku_id']);
        $num = intval($data['value']);
        $user_id = input('post.user_id',0,'intval');
        $remark = str_filter($data['remark']);
        if($num <= 0)throw new Exception('充值数量错误');
        ##数据
        $user = User::get(['user_id'=>$user_id],['grade']);
        $goods = GoodsSku::get(['goods_sku_id'=>$goods_sku_id], ['goods.image']);
        $check = UserGoodsStock::checkStock($user, $goods['goods_id'], $goods_sku_id, $num);
        if(!$check['isStockEnough']){
            throw new Exception('库存不足');
        }
        ##判断是否是补货订单
        $weight = Grade::getWeightByGradeId($check['grade_id']);
        if($weight < GradeSize::VIP)throw new Exception('这里仅支持补货,消费订单请在小程序下单');
        $order_data['user_grade_id'] = $check['grade_id'];
        $order_data['supply_user_id'] = $check['supplyUserId'];
        $order_data['supply_user_grade_id'] = $check['supply_user_grade_id'];
        $supply_user_id = $check['supplyUserId'];
        $rebateUser = self::getRebateUser($user_id, $goods['goods_id'], $num, $supply_user_id, $goods['goods']['rebate_type']);
        if(!empty($rebateUser)){
            $rebateMoney = self::sumRebate($rebateUser);
            $rebateUsers = self::combineRebateUser($rebateUser);
        }
        $order_data['rebate_user_id'] = isset($rebateUsers) ? $rebateUsers : "";
        $order_data['rebate_money'] = isset($rebateMoney)? $rebateMoney : 0;
        $order_data['rebate_info'] = !empty($rebateUser)? json_encode($rebateUser) : "";
        $order_data['sale_type'] = $goods['goods']['sale_type']; ##商品类型
        $order_data['free_freight_num'] = $goods['goods']['free_freight_num']; ##免配送费商品基数
        $order_data['goods_num'] = $num; ##商品数量

        ##获取商品价格
        $price = GoodsGrade::getGoodsPrice($check['grade_id'], $goods['goods_id']);
        $total_price = $price * $num;

        ##订单信息
        $order_no = \app\common\service\Order::createOrderNo();
        $order_data = array_merge($order_data, [
            'user_id' => $user_id,
            'order_no' => $order_no,
            'out_trade_no' => $order_no,
            'total_price' => $total_price,
            'order_price' => $total_price,
            'coupon_id' => 0,
            'coupon_money' => 0,
            'points_money' => 0,
            'points_num' => 0,
            'pay_price' => $total_price,
            'delivery_type' => 30,
            'pay_type' => 30,
            'buyer_remark' => $remark,
            'order_source' => 10,
            'order_source_id' => 0,
            'points_bonus' => 0,
            'wxapp_id' => self::$wxapp_id,
        ]);
        $goods_2 = Goods::detail($goods['goods_id'])->toArray();
        $goods_attr = Goods::getGoodsSku($goods_2,$goods['spec_sku_id']);
        ##订单商品信息
        $order_goods_data = [
            'user_id' => $user_id,
            'wxapp_id' => self::$wxapp_id,
            'goods_id' => $goods['goods_id'],
            'goods_name' => $goods['goods']['goods_name'],
            'image_id' => $goods['goods']['image'][0]['image_id'],
            'deduct_stock_type' => $goods['goods']['deduct_stock_type'],
            'spec_type' => $goods['goods']['spec_type'],
            'spec_sku_id' => $goods['spec_sku_id'],
            'goods_sku_id' => $goods['goods_sku_id'],
            'goods_attr' =>  $goods_attr ? $goods_attr['goods_attr'] : '' ,
            'content' => $goods['goods']['content'],
            'goods_no' => $goods['goods_no'],
            'goods_price' => $price,
            'line_price' => $goods['line_price'],
            'goods_weight' => $goods['goods_weight'],
            'is_user_grade' => 0,
            'grade_ratio' => 0,
            'grade_goods_price' => $price,
            'grade_total_money' => $total_price,
            'coupon_money' => 0,
            'points_money' => 0.00,
            'points_num' => 0,
            'points_bonus' => 0,
            'total_num' => $num,
            'total_price' => $total_price,
            'total_pay_price' => $total_price,
            'is_ind_dealer' => $goods['goods']['is_ind_dealer'],
            'dealer_money_type' => $goods['goods']['dealer_money_type'],
            'first_money' => $goods['goods']['first_money'],
            'second_money' => $goods['goods']['second_money'],
            'third_money' => $goods['goods']['third_money'],
            'is_add_integral' => $goods['goods']['is_add_integral'],
            'integral_weight' => $goods['goods']['integral_weight'],
            'sale_type' => $goods['goods']['sale_type']
        ];
        ##创建订单
        Db::startTrans();
        try{
            $orderModel = new Order();
            $res = $orderModel->isUpdate(false)->allowField(true)->save($order_data);
            if($res === false)throw new Exception('订单创建失败');
            $order_id = $orderModel->getLastInsID();
            $order_goods_data['order_id'] = $order_id;
            $res = (new OrderGoods())->isUpdate(false)->allowField(true)->save($order_goods_data);
            if($res === false)throw new Exception('订单创建失败.');
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
        ##支付成功操作
        $PaySuccess = new PaySuccess($order_no);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(PayTypeEnum::ADMIN);
        if (!$status) {
            throw new Exception($PaySuccess->getError());
        }
        return true;
    }

    /**
     * 活动充值库存
     * @param $data
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function rechargeToGrade($data){
        ##参数
        $grade_id = intval($data['grade_id']);
        $goods_sku_id = intval($data['goods_sku_id']);
        $num = intval($data['value']);
        $user_id = input('post.user_id',0,'intval');
        $remark = str_filter($data['remark']);
        if($num <= 0)throw new Exception('充值数量错误');
        ##数据
        ##判断是否是补货订单
        $weight = Grade::getWeightByGradeId($grade_id);
        if($weight < GradeSize::VIP)throw new Exception('这里仅支持补货,消费订单请在小程序下单');
        Db::startTrans();
        try{
            ##升级用户等级
            $res = $this->updateGrade(['grade_id'=>$grade_id, 'remark'=>$remark]);
            if(!$res)throw new Exception('用户等级操作失败');
            ##订单
            $user = User::get(['user_id'=>$user_id],['grade']);
            $goods = GoodsSku::get(['goods_sku_id'=>$goods_sku_id], ['goods.image']);
            $check = UserGoodsStock::checkStock($user, $goods['goods_id'], $goods_sku_id, $num);
            if(!$check['isStockEnough']){
                throw new Exception('库存不足');
            }
            $order_data['user_grade_id'] = $grade_id;
            $order_data['supply_user_id'] = $check['supplyUserId'];
            $order_data['supply_user_grade_id'] = $check['supply_user_grade_id'];
            $supply_user_id = $check['supplyUserId'];
            $rebateUser = self::getRebateUser($user_id, $goods['goods_id'], $num, $supply_user_id, $goods['goods']['rebate_type']);
            if(!empty($rebateUser)){
                $rebateMoney = self::sumRebate($rebateUser);
                $rebateUsers = self::combineRebateUser($rebateUser);
            }
            $order_data['rebate_user_id'] = isset($rebateUsers) ? $rebateUsers : "";
            $order_data['rebate_money'] = isset($rebateMoney)? $rebateMoney : 0;
            $order_data['rebate_info'] = !empty($rebateUser)? json_encode($rebateUser) : "";
            $order_data['sale_type'] = $goods['goods']['sale_type']; ##商品类型
            $order_data['free_freight_num'] = $goods['goods']['free_freight_num']; ##免配送费商品基数
            $order_data['goods_num'] = $num; ##商品数量

            ##获取商品价格
            $price = GoodsGrade::getGoodsPrice($check['grade_id'], $goods['goods_id']);
            $total_price = $price * $num;

            ##订单信息
            $order_no = \app\common\service\Order::createOrderNo();
            $order_data = array_merge($order_data, [
                'user_id' => $user_id,
                'order_no' => $order_no,
                'out_trade_no' => $order_no,
                'total_price' => $total_price,
                'order_price' => $total_price,
                'coupon_id' => 0,
                'coupon_money' => 0,
                'points_money' => 0,
                'points_num' => 0,
                'pay_price' => $total_price,
                'delivery_type' => 30,
                'pay_type' => 30,
                'buyer_remark' => $remark,
                'order_source' => 10,
                'order_source_id' => 0,
                'points_bonus' => 0,
                'wxapp_id' => self::$wxapp_id,
            ]);
            $goods_2 = Goods::detail($goods['goods_id'])->toArray();
            $goods_attr = Goods::getGoodsSku($goods_2,$goods['spec_sku_id']);
            ##订单商品信息
            $order_goods_data = [
                'user_id' => $user_id,
                'wxapp_id' => self::$wxapp_id,
                'goods_id' => $goods['goods_id'],
                'goods_name' => $goods['goods']['goods_name'],
                'image_id' => $goods['goods']['image'][0]['image_id'],
                'deduct_stock_type' => $goods['goods']['deduct_stock_type'],
                'spec_type' => $goods['goods']['spec_type'],
                'spec_sku_id' => $goods['spec_sku_id'],
                'goods_sku_id' => $goods['goods_sku_id'],
                'goods_attr' =>  $goods_attr ? $goods_attr['goods_attr'] : '' ,
                'content' => $goods['goods']['content'],
                'goods_no' => $goods['goods_no'],
                'goods_price' => $price,
                'line_price' => $goods['line_price'],
                'goods_weight' => $goods['goods_weight'],
                'is_user_grade' => 0,
                'grade_ratio' => 0,
                'grade_goods_price' => $price,
                'grade_total_money' => $total_price,
                'coupon_money' => 0,
                'points_money' => 0.00,
                'points_num' => 0,
                'points_bonus' => 0,
                'total_num' => $num,
                'total_price' => $total_price,
                'total_pay_price' => $total_price,
                'is_ind_dealer' => $goods['goods']['is_ind_dealer'],
                'dealer_money_type' => $goods['goods']['dealer_money_type'],
                'first_money' => $goods['goods']['first_money'],
                'second_money' => $goods['goods']['second_money'],
                'third_money' => $goods['goods']['third_money'],
                'is_add_integral' => $goods['goods']['is_add_integral'],
                'integral_weight' => $goods['goods']['integral_weight'],
                'sale_type' => $goods['goods']['sale_type']
            ];
            $orderModel = new Order();
            $res = $orderModel->isUpdate(false)->allowField(true)->save($order_data);
            if($res === false)throw new Exception('订单创建失败');
            $order_id = $orderModel->getLastInsID();
            $order_goods_data['order_id'] = $order_id;
            $res = (new OrderGoods())->isUpdate(false)->allowField(true)->save($order_goods_data);
            if($res === false)throw new Exception('订单创建失败.');
            Db::commit();
        }catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
        ##支付成功操作
        $PaySuccess = new PaySuccess($order_no);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(PayTypeEnum::ADMIN,[],0);
        if (!$status) {
            throw new Exception($PaySuccess->getError());
        }
        return true;

    }

    /**
     * 指定的商品规格信息
     * @param static $goods 商品详情
     * @param int $specSkuId
     * @return array|bool
     */
    public static function getGoodsSku($goods, $specSkuId)
    {
        // 获取指定的sku
        $goodsSku = [];
        foreach ($goods['sku'] as $item) {
            if ($item['spec_sku_id'] == $specSkuId) {
                $goodsSku = $item;
                break;
            }
        }
        if (empty($goodsSku)) {
            return false;
        }
        // 多规格文字内容
        $goodsSku['goods_attr'] = '';
        if ($goods['spec_type'] == 20) {
            $specRelData = helper::arrayColumn2Key($goods['spec_rel'], 'spec_value_id');
            $attrs = explode('_', $goodsSku['spec_sku_id']);
            foreach ($attrs as $specValueId) {
                $goodsSku['goods_attr'] .= $specRelData[$specValueId]['spec']['spec_name'] . ':'
                    . $specRelData[$specValueId]['spec_value'] . '; ';
            }
        }
        return $goodsSku;
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
     * 修改用户等级
     * @param $data
     * @return mixed
     */
    public function updateGrade($data)
    {
        // 变更前的等级id
        $oldGradeId = $this['grade_id'];
        if($data['grade_id'] == $oldGradeId)return true;
        return $this->transaction(function () use ($oldGradeId, $data) {
            ##获取新等级的信息
            $levelNew = Grade::getGradeInfo($data['grade_id']);
            $levelOld = Grade::getGradeInfo($this['grade_id']);
            $changeDirection = $levelNew['weight'] > $levelOld['weight'] ? 10 : 20 ;
            ##以前积分
            $oldIntegral = $this['integral'];
            $newIntegral = $levelNew['upgrade_integral'];

            ##更新用户的等级和积分
            $status = $this->save([
                'grade_id' => $data['grade_id'],
                'integral' => $levelNew['upgrade_integral']
            ]);
            // 新增用户等级修改记录
            if ($status) {
                (new GradeLogModel)->record([
                    'user_id' => $this['user_id'],
                    'old_grade_id' => $oldGradeId,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => ChangeTypeEnum::ADMIN_USER,
                    'remark' => $data['remark'],
                    'change_direction' => $changeDirection
                ]);
                $gradeLogId = (new GradeLogModel)->getLastInsID();

                ##增加积分变更记录
                IntegralLog::addLog([
                    'user_id' => $this['user_id'],
                    'balance_integral' => $oldIntegral,
                    'change_integral' => abs($newIntegral - $oldIntegral),
                    'change_direction' => $changeDirection,
                    'change_type' => 20
                ]);
                $integralLogId = (new IntegralLog)->getLastInsID();
                GradeLogModel::where(['log_id'=>$gradeLogId])->setField('integral_log_id', $integralLogId);

                ##推荐升级
                $params = [
                    'user_id' => $this['invitation_user_id']
                ];
                Hook::listen('agent_instant_grade',$params);
            }

            return $status !== false;
        });
    }

    /**
     * 消减用户的实际消费金额
     * @param $userId
     * @param $expendMoney
     * @return int|true
     * @throws \think\Exception
     */
    public function setDecUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->setDec('expend_money', $expendMoney);
    }

    /**
     * 增加用户积分
     * @param $userId
     * @param $goodsId
     * @param $diffStock
     * @param $stockLogId
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function incUserIntegralByGoodsId($userId, $goodsId, $diffStock){
        ##获取商品积分信息
        $goodsInfo = Goods::getAgentGoodsInfo($goodsId);
        if($goodsInfo['is_add_integral'] != 1 || !$goodsInfo['integral_weight'])return 0; ##不需要增加积分

        $diffIntegral = $diffStock * $goodsInfo['integral_weight'];
        ##获取用户当前的积分
        $oldIntegral = self::where(['user_id'=>$userId])->value('integral');
        $finalIntegral = $oldIntegral + $diffIntegral;
        Db::startTrans();
        try{
            ##更新积分
            self::where(['user_id'=>$userId])->setField('integral', $oldIntegral + $finalIntegral);
            ##增加积分更新记录
            $res = IntegralLog::addLog([
                'user_id' => $userId,
                'balance_integral' => $oldIntegral,
                'change_integral' => $diffIntegral
            ]);
            if($res === false)throw new Exception('积分变更日志写入失败');
            $integralLogId = (new IntegralLog)->getLastInsID();
            ## 刷新用户等级
            $options = [
                'user_id' => $userId,
                'integral_log_id' => $integralLogId
            ];
            ### 刷新用户
            Hook::listen('user_instant_grade',$options);

            Db::commit();
            return (int)$integralLogId;
        }catch(Exception $e){
            Db::rollback();;
            return $e->getMessage();
        }
    }

    /**
     * 返还冻结的余额
     * @param $user_id
     * @param $money
     * @param $reason
     * @return bool|string
     */
    public static function backFreezeMoney($user_id, $money, $reason){
        Db::startTrans();
        try{
            ##返还用户可提现余额,减少用户冻结中余额
            $res = self::update(['balance'=>['inc', $money], 'freeze_money'=>['dec', $money]], compact('user_id'));
            if($res === false)throw new Exception('余额返还失败');
            ##添加余额变动记录
            BalanceLog::add(Scene::WITHDRAW_REFUSE, [
                'money' => $money,
                'user_id' => $user_id
            ], $reason);
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 减少冻结的余额,增加总提现金额
     * @param $user_id
     * @param $money
     */
    public static function totalMoney($user_id, $money){
        ##增加用户已提现金额,减少用户冻结中余额
        self::update(['withdraw_money'=>['inc', $money], 'freeze_money'=>['dec', $money]], compact('user_id'));
    }

    /**
     * 通过昵称模糊查询用户id
     * @param $nick_name
     * @return array
     */
    public static function getLikeUserByName($nick_name){
        return self::where(['nickName'=>['LIKE', "%{$nick_name}%"]])->column('user_id');
    }

    /**
     * 转换团队
     * @return bool|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exchangeTeam(){
        ##验证
        $validate = new UserValid();
        $res = $validate->scene('exchange_team')->check(request()->post());
        if(!$res)throw new Exception($validate->getError());
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $exchange_user_id = input('post.exchange_user_id',0,'intval');
        if($user_id == $exchange_user_id)throw new Exception('非法操作');
        ##执行操作
        return self::doExchangeTeam($user_id, $exchange_user_id);
    }

    /**
     * 团队列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function teamLists(){
        $params = [
            'user_id' => input('user_id',0,'intval'),
            'keywords' => input('keywords','','str_filter'),
            'grade_id' => input('grade_id',0,'intval')
        ];
        $where = [
            'relation' => ['LIKE', "%-{$params['user_id']}-%"]
        ];
        if($params['keywords']){
            $where['nickName|mobile'] = ['LIKE', "%{$params['keywords']}%"];
        }
        if($params['grade_id']){
            $where['grade_id'] = $params['grade_id'];
        }
        $list = $this
            ->where($where)
            ->with(
                [
                    'grade' => function(Query $query){
                        $query->field(['grade_id', 'name']);
                    }
                ]
            )
            ->field(['user_id', 'nickName', 'mobile', 'avatarUrl', 'grade_id', 'invitation_user_id', 'create_time'])
            ->paginate(15,false,['query'=>\request()->request()]);

        ##等级列表
        $grade_list = Grade::getUsableList();

        return array_merge(compact('list','grade_list'), $params);
    }

    /**
     * 后台线下返利
     * @return bool|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function beStrategy(){
        ##验证
        $validate = new UserValid();
        $res = $validate->scene('rebate')->check(input());
        if(!$res)throw new Exception($validate->getError());
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $goods_id = input('post.goods_id',0,'intval');
        $goods_sku_id = input('post.goods_sku_id',0,'intval');
        $num = input('post.num',0,'intval');
        $remark = input('post.remark','','str_filter');
        if($num <= 0)throw new Exception('商品数量错误');
        ##操作
        $goods = GoodsSku::get(['goods_sku_id'=>$goods_sku_id], ['goods.image']);
        if($goods['stock_num'] < $num)throw new Exception('库存不足');
        $user = User::get(['user_id'=>$user_id], ['grade']);
        $strategy_grade_id = Grade::getGradeId(GradeSize::STRATEGY);
        if($user['grade_id'] == $strategy_grade_id)throw new Exception('用户已经是战略董事了');
        if($user['grade']['weight'] > GradeSize::STRATEGY)throw new Exception('用户目前是更高等级');
        ##订单数据
        $order_data['user_grade_id'] = $strategy_grade_id;
        $order_data['supply_user_id'] = 0;
        $order_data['supply_user_grade_id'] = 0;
        Db::startTrans();
        try{
            ##改变用户等级
            $userModel = self::detail($user_id);
            $res = $userModel->updateGrade(['grade_id'=>$strategy_grade_id,'remark'=>'直接发展战略董事']);
            if(!$res)throw new Exception('修改用户等级失败');
            ##创建订单
//            $supply_user_id = self::getSupplyUserId($user['relation'],GradeSize::STRATEGY);
            $rebateUser = self::getStrategyRebateUser($user_id, $num, $goods['goods']['rebate_type']);
            if(!empty($rebateUser)){
                $rebateMoney = self::sumRebate($rebateUser);
                $rebateUsers = self::combineRebateUser($rebateUser);
            }
            $order_data['rebate_user_id'] = isset($rebateUsers) ? $rebateUsers : "";
            $order_data['rebate_money'] = isset($rebateMoney)? $rebateMoney : 0;
            $order_data['rebate_info'] = !empty($rebateUser)? json_encode($rebateUser) : "";
            $order_data['sale_type'] = $goods['goods']['sale_type']; ##商品类型
            $order_data['free_freight_num'] = $goods['goods']['free_freight_num']; ##免配送费商品基数
            $order_data['goods_num'] = $num; ##商品数量

            ##获取商品价格
            $price = GoodsGrade::getGoodsPrice($strategy_grade_id, $goods['goods_id']);
            $total_price = $price * $num;

            ##订单信息
            $order_no = \app\common\service\Order::createOrderNo();
            $order_data = array_merge($order_data, [
                'user_id' => $user_id,
                'order_no' => $order_no,
                'out_trade_no' => $order_no,
                'total_price' => $total_price,
                'order_price' => $total_price,
                'coupon_id' => 0,
                'coupon_money' => 0,
                'points_money' => 0,
                'points_num' => 0,
                'pay_price' => $total_price,
                'delivery_type' => 30,
                'pay_type' => 30,
                'buyer_remark' => $remark,
                'order_source' => 10,
                'order_source_id' => 0,
                'points_bonus' => 0,
                'wxapp_id' => self::$wxapp_id,
            ]);

            $goods_2 = Goods::detail($goods['goods_id'])->toArray();
            $goods_attr = Goods::getGoodsSku($goods_2,$goods['spec_sku_id']);
            ##订单商品信息
            $order_goods_data = [
                'user_id' => $user_id,
                'wxapp_id' => self::$wxapp_id,
                'goods_id' => $goods['goods_id'],
                'goods_name' => $goods['goods']['goods_name'],
                'image_id' => $goods['goods']['image'][0]['image_id'],
                'deduct_stock_type' => $goods['goods']['deduct_stock_type'],
                'spec_type' => $goods['goods']['spec_type'],
                'spec_sku_id' => $goods['spec_sku_id'],
                'goods_sku_id' => $goods['goods_sku_id'],
                'goods_attr' =>  $goods_attr ? $goods_attr['goods_attr'] : '' ,
                'content' => $goods['goods']['content'],
                'goods_no' => $goods['goods_no'],
                'goods_price' => $price,
                'line_price' => $goods['line_price'],
                'goods_weight' => $goods['goods_weight'],
                'is_user_grade' => 0,
                'grade_ratio' => 0,
                'grade_goods_price' => $price,
                'grade_total_money' => $total_price,
                'coupon_money' => 0,
                'points_money' => 0.00,
                'points_num' => 0,
                'points_bonus' => 0,
                'total_num' => $num,
                'total_price' => $total_price,
                'total_pay_price' => $total_price,
                'is_ind_dealer' => $goods['goods']['is_ind_dealer'],
                'dealer_money_type' => $goods['goods']['dealer_money_type'],
                'first_money' => $goods['goods']['first_money'],
                'second_money' => $goods['goods']['second_money'],
                'third_money' => $goods['goods']['third_money'],
                'is_add_integral' => $goods['goods']['is_add_integral'],
                'integral_weight' => $goods['goods']['integral_weight'],
                'sale_type' => $goods['goods']['sale_type']
            ];

            $orderModel = new Order();
            $res = $orderModel->isUpdate(false)->allowField(true)->save($order_data);
            if($res === false)throw new Exception('订单创建失败');
            $order_id = $orderModel->getLastInsID();
            $order_goods_data['order_id'] = $order_id;
            $res = (new OrderGoods())->isUpdate(false)->allowField(true)->save($order_goods_data);
            if($res === false)throw new Exception('订单创建失败.');
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

        ##支付成功操作
        $PaySuccess = new PaySuccess($order_no);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(PayTypeEnum::ADMIN,[],0);
        if (!$status) {
            throw new Exception($PaySuccess->getError());
        }
        return true;
    }

    /**
     * 获取用户余额
     * @param $user_id
     * @return mixed
     */
    public static function getUserBalance($user_id){
        return self::where(['user_id'=>$user_id])->value('balance');
    }

    /**
     * 获取器 -- 下级人数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getMemberNumAttr($user_id){
        return self::where(['relation'=>['LIKE', "%-{$user_id}-%"]])->count('user_id');
    }

}
