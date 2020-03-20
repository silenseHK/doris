<?php

namespace app\common\model;

use app\common\enum\user\balanceLog\Scene;
use app\common\model\user\BalanceLog;
use app\common\model\user\Grade;
use app\common\model\user\IntegralLog;
use app\common\model\user\PointsLog as PointsLogModel;
use think\Exception;
use think\Hook;

/**
 * 用户模型类
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'user';

    // 性别
    private $gender = ['未知', '男', '女'];

    /**
     * 关联会员等级表
     * @return \think\model\relation\BelongsTo
     */
    public function grade()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\user\\Grade");
    }

    /**
     * 关联收货地址表
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany('UserAddress');
    }

    /**
     * 关联收货地址表 (默认地址)
     * @return \think\model\relation\BelongsTo
     */
    public function addressDefault()
    {
        return $this->belongsTo('UserAddress', 'address_id');
    }

    /**
     * 显示性别
     * @param $value
     * @return mixed
     */
    public function getGenderAttr($value)
    {
        return $this->gender[$value];
    }

    /**
     * 获取用户信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = ['address', 'addressDefault'])
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::get($filter, $with);
    }

    /**
     * 累积用户的实际消费金额
     * @param $userId
     * @param $expendMoney
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
    }

    /**
     * 指定会员等级下是否存在用户
     * @param $gradeId
     * @return bool
     */
    public static function checkExistByGradeId($gradeId)
    {
        $model = new static;
        return !!$model->where('grade_id', '=', (int)$gradeId)->value('user_id');
    }

    /**
     * 累积用户总消费金额
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPayMoney($money)
    {
        return $this->setInc('pay_money', $money);
    }

    /**
     * 累积用户实际消费的金额 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncExpendMoney($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分数量 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncPoints($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('points', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分
     * @param $points
     * @param $describe
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPoints($points, $describe)
    {
        // 新增积分变动明细
        PointsLogModel::add([
            'user_id' => $this['user_id'],
            'value' => $points,
            'describe' => $describe,
        ]);
        // 更新用户可用积分
        return $this->setInc('points', $points);
    }

    /**
     * 获取用户当前信息
     * @param $userId
     * @param $goodsId
     * @param $num
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRecentUserInfo($userId, $goodsId, $num){
        #获取商品信息
        $goodsInfo = Goods::getGoodsAgentInfo($goodsId);
        if($goodsInfo['sale_type'] != 1)throw new Exception('商品销售类型非层及代理');
        ##计算获取的积分
        $addIntegral = 0;
        if($goodsInfo['is_add_integral'] == 1){
            $addIntegral = $goodsInfo['integral_weight'] * $num;
        }
        #获取用户信息
        $userInfo = self::where(['user_id'=>$userId])->field(['grade_id', 'relation', 'integral'])->find();
        $finalIntegral = $userInfo['integral'] + $addIntegral;
        $userInfo['final_integral'] = $finalIntegral;
        return $userInfo;
    }

    /**
     * 获取购买商品价格
     * @param $userId
     * @param $goodsId
     * @param $num
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentGoodsPrice($userId, $goodsId, $num){
        ##获取当前等级信息
        $userInfo = self::getRecentUserInfo($userId, $goodsId, $num);
        $gradeInfo = Grade::getRecentGrade($userInfo['final_integral']);
        ##获取商品购买价格
        return GoodsGrade::getGoodsPrice($gradeInfo['grade_id'], $goodsId);
    }

    /**
     * 获取商品供应用户
     * @param $userId
     * @param $goodsId
     * @param $num
     * @return bool|int|string
     */
    public static function getSupplyGoodsUser($userId, $goodsId, $num){
        try{
            #获取商品信息
            $userInfo = self::getRecentUserInfo($userId, $goodsId, $num);
            ##获取最新等级
            $gradeInfo = Grade::getRecentGrade($userInfo['final_integral']);
            ##获取商品供货方
            return self::getSupplyUserId($userInfo['relation'], $gradeInfo['weight']);
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 获取代理商品价格和供货用户id
     * @param $userId
     * @param $goodsId
     * @param $num
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAgentGoodsPriceSupplyUser($userId, $goodsId, $num){
        #获取商品信息
        $userInfo = self::getRecentUserInfo($userId, $goodsId, $num);
        ##获取最新等级
        $gradeInfo = Grade::getRecentGrade($userInfo['final_integral']);

        $price = GoodsGrade::getGoodsPrice($gradeInfo['grade_id'], $goodsId);
        $supplyUserId = self::getSupplyUserId($userInfo['relation'], $gradeInfo['weight']);
        return compact('price','supplyUserId');
    }

    /**
     * 获取供应商品用户id
     * @param $relation  *关系网
     * @param $weight  *当前等级权重
     * @return bool|int  返回0表示由平台发货
     */
    public static function getSupplyUserId($relation, $weight){
        if(!$relation)return 0;
        ##获取供货人的等级id
        $applyGradeIds = Grade::getApplyGrade($weight);
        $relation = explode('_', $relation);
        ##获取供应人id
        $relation_ids = implode(',', $relation);
        $user_id = self::where(['user_id'=>['IN', $relation], 'grade_id'=>['IN', $applyGradeIds], 'is_delete'=>0])->orderRaw("field(user_id," . $relation_ids . ")")->value('user_id');
        return $user_id ? : 0;
    }

    /**
     * 判断是否是最高等级
     * @param $gradeId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkIsHighestGrade($gradeId){
        ##获取最高等级会员信息
        $highestGrade = Grade::getHighestGrade();
        return $gradeId == $highestGrade['grade_id'];
    }

    /**
     * 增加用户积分
     * @param $goodsList
     * @return int|string
     * @throws Exception
     */
    public function setIncIntegral($goodsList){
        $model = $this;
        $diffIntegral = 0;
        ##获取积分
        foreach($goodsList as $goods){
            if($goods['is_add_integral']){
                $diffIntegral += $goods['integral_weight'] * $goods['total_num'];
            }
        }
        $oldIntegral = $model['integral'];
        if($diffIntegral > 0){
            $this->setInc('integral', $diffIntegral);
            $IntegralModel = (new IntegralLog);
            $IntegralModel->save([
                'user_id' => $model['user_id'],
                'balance_integral' => $oldIntegral,
                'change_integral' => $diffIntegral
            ]);
            $integralLogId = $IntegralModel->getLastInsID();

            return $integralLogId;
        }
        return 0;
    }

    /**
     * 增加用户库存 && 减少供应用户库存
     * @param $model
     * @param $integralLogId
     * @throws Exception
     */
    public function addGoodsStock($model, $integralLogId){
        $goodsList = $model['goods'];
        $data = $stock = $decStock = $rebate = [];
        $balance = 0;
        foreach($goodsList as $goods){
            if($goods['sale_type'] == 1){ ##层级代理商品
                if($model['delivery_type']['value'] == 30){ ##用户选择补充库存
                    $data[] = [
                        'user_id' => $this['user_id'],
                        'goods_id' => $goods['goods_id'],
                        'balance_stock' => UserGoodsStock::getStock($this['user_id'], $goods['goods_id']),
                        'change_num' => $goods['total_num'],
                        'opposite_user_id' => $model['supply_user_id'],  //发货人id
                        'remark' => '用户补货',
                        'integral_weight' => $goods['integral_weight'],
                        'integral_log_id' => $integralLogId
                    ];
                    if(!isset($stock[$goods['goods_id']]))$stock[$goods['goods_id']] = 0;
                    $stock[$goods['goods_id']] += $goods['total_num'];
                }
                ##减少库存
                if($model['supply_user_id'] >0){ ##供货人非平台
                    if(!isset($decStock[$goods['goods_id']]))$decStock[$goods['goods_id']] = 0;
                    $decStock[$goods['goods_id']] += $goods['total_num'];
                    $balance += $goods['total_pay_price'];
                }
                if(!isset($rebate[$goods['goods_id']]))$rebate[$goods['goods_id']] = 0;
                $rebate[$goods['goods_id']] += $goods['total_num'] ;
            }
        }
        ##增加购买人库存
        if(!empty($stock)){
            foreach($stock as $key => $sto){
                UserGoodsStock::editStock($this['user_id'], $key, $sto);
            }
            UserGoodsStockLog::insertAllData($data);
        }
        ##减少供货人库存
        if(!empty($decStock)){
            $desData = [];
            foreach($decStock as $key => $val){
                ##获取以前的库存
                $supplyUserId = $model['supply_user_id'];
                $desData[] = [
                    'user_id' => $model['supply_user_id'],
                    'goods_id' => $key,
                    'balance_stock' => UserGoodsStock::getStock($supplyUserId, $key),
                    'change_num' => $val,
                    'change_type' => 10,
                    'change_direction' => 20,
                    'opposite_user_id' => $this['user_id'],
                    'remark' => '出货减库存'
                ];
                ##减少库存
                UserGoodsStock::editStock($model['supply_user_id'], $key, $val, 'dec');
            }
            ##插入库存变更日志
            UserGoodsStockLog::insertAllData($desData);
            if($model['delivery_type']['value'] == 30){
                ##增加可用余额
                self::addBalanceByOrder($model['supply_user_id'], $model['order_id'], $balance, $model['order_no']);
            }
        }
        ##返利 (补货的情况下)
        if(!empty($rebate) && $model['delivery_type']['value'] == 30){
            ##查找获利人
            $rebateUserId = $model['rebate_user_id'];
            if($rebateUserId){
                $rebateMoney = $model['rebate_money'];
                ##返利给用户
                self::addBalanceByOrder($rebateUserId, $model['order_id'], $rebateMoney, $model['order_no'], Scene::REBATE);
                if($model['supply_user_id'] > 0){
                    self::reduceBalanceByOrder($model['supply_user_id'], $model['order_id'], $rebateMoney, $model['order_no']);
                }
                ##添加返利日志
                RebateLog::addLog([
                    'user_id' => $rebateUserId,
                    'supply_user_id' => $model['supply_user_id'],
                    'buy_user_id' => $this['user_id'],
                    'balance' => $rebateMoney,
                    'order_id' => $model['order_id']
                ]);
            }
        }

        ##刷新用户等级
        if($model['delivery_type']['value'] == 30){
            $options = [
                'user_id' => $this['user_id'],
                'integral_log_id' => $integralLogId
            ];
            ### 刷新用户会员等级
            Hook::listen('user_instant_grade',$options);
        }

    }

    /**
     * 用户发货后增加余额
     * @param $userId
     * @param $orderId
     * @param $balance
     * @param $orderNo
     * @param $scene
     * @throws Exception
     */
    public static function addBalanceByOrder($userId, $orderId, $balance, $orderNo, $scene=Scene::SALE){
        ##增加余额
        self::where(['user_id'=>$userId])->setInc('balance', $balance);
        ##增加余额变动记录
        BalanceLog::add($scene, [
            'user_id' => $userId,
            'money' => $balance,
            'order_id' => $orderId
        ],"订单号{$orderNo}");
    }

    /**
     * 出货方返利扣余额
     * @param $userId
     * @param $orderId
     * @param $balance
     * @param $orderNo
     * @throws Exception
     */
    public static function reduceBalanceByOrder($userId, $orderId, $balance, $orderNo){
        ##减少余额
        self::where(['user_id'=>$userId])->setDec('balance', $balance);
        ##增加余额变动记录
        BalanceLog::add(Scene::PAY_REBATE, [
            'user_id' => $userId,
            'money' => $balance,
            'order_id' => $orderId
        ],"订单号{$orderNo}");
    }

    /**
     * 获取获利人user_id
     * @param $userId
     * @param $supplyUserId
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRebateUser($userId, $supplyUserId){
        $userData = self::alias('u')
            ->join('user_grade ug','u.grade_id = ug.grade_id','LEFT')
            ->where(['u.user_id'=>$userId])
            ->field(['u.relation', 'ug.grade_type', 'u.grade_id', 'ug.weight'])
            ->find();
        if(!$userData['relation'])return [];
        $relation = explode('_',$userData['relation']);
        if(!$relation[0])return [];
        ##获取
        if($supplyUserId > 0){
            $filter = [];
            foreach($relation as $rel){
                if($rel == $supplyUserId){
                    break;
                }
                $filter[] = $rel;
            }
        }else{
            $filter = $relation;
        }
        $rebateGradeIds = Grade::getRebateGrade($userData['grade_type']);
        ##查找获利人
        $orderFilter = implode(',', $filter);
        $rebateUser = self::where(['user_id'=>['IN', $filter], 'grade_id'=>['IN', $rebateGradeIds], 'is_delete'=>0])->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->find();
        return $rebateUser ? $rebateUser->toArray() : [];
    }

}
