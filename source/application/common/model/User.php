<?php

namespace app\common\model;

use app\common\enum\user\balanceLog\Scene;
use app\common\enum\user\grade\GradeSize;
use app\common\enum\user\grade\GradeType;
use app\common\enum\user\grade\RebateConfig;
use app\common\model\user\BalanceLog;
use app\common\model\user\Grade;
use app\common\model\user\IntegralLog;
use app\common\model\user\PointsLog as PointsLogModel;
use think\db\Query;
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
        $userInfo = self::where(['user_id'=>$userId])->field(['grade_id', 'relation', 'integral'])->with([
            'grade' => function(Query $query){
                $query->field(['grade_id', 'grade_type', 'weight']);
            }
        ])->find();
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
        $gradeInfo = Grade::getRecentGrade($userInfo['final_integral'], ['weight'=>$userInfo['grade']['weight'], 'grade_id'=>$userInfo['grade_id']]);
        ##获取商品购买价格
        return GoodsGrade::getGoodsPrice($gradeInfo['grade_id'], $goodsId);
    }

    /**
     * 获取商品供应用户
     * @param $userId
     * @param $goodsId
     * @param $num
     * @return array|string
     */
    public static function getSupplyGoodsUser($userId, $goodsId, $num){
        try{
            #获取商品信息
            $userInfo = self::getRecentUserInfo($userId, $goodsId, $num);
            ##如果是董事或者合伙人则直接由平台发货
            if($userInfo['grade']['grade_type'] == GradeType::HIDE){
                return [
                    'grade_id' => $userInfo['grade_id'],
                    'supply_user_id' => 0
                ];
            }
            ##获取最新等级
            $gradeInfo = Grade::getRecentGrade($userInfo['final_integral'],
                [
                    'grade_id'=>$userInfo['grade_id'],
                    'weight'=>$userInfo['grade']['weight'],
                    'grade_type' => $userInfo['grade']['grade_type'],
                ]);
            ##获取商品供货方
            $supplyUserId = self::getSupplyUserId($userInfo['relation'], $gradeInfo['weight']);
            return [
                'grade_id' => $gradeInfo['grade_id'],
                'supply_user_id' => $supplyUserId
            ];
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
        $relation = trim($relation,'_');
        if(!$relation)return 0;
        ##获取供货人的等级id
        $applyGradeIds = Grade::getApplyGrade($weight);
        if(empty($applyGradeIds))return 0;
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
        $data = $stock = $decStock = [];
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
        $rebate = $model['rebate_info'];
        if($rebate && $model['delivery_type']['value'] == 30){
            $rebate = json_decode($rebate,true);
            if(!empty($rebate)){
                foreach($rebate as $item){
                    ##返利给用户
                    self::addBalanceByOrder($item['user_id'], $model['order_id'], $item['money'], $model['order_no'],Scene::REBATE);
                    ##添加返利日志
                    RebateLog::addLog([
                        'user_id' => $item['user_id'],
                        'supply_user_id' => $model['supply_user_id'],
                        'buy_user_id' => $this['user_id'],
                        'balance' => $item['money'],
                        'order_id' => $model['order_id'],
                        'remark' => $item['text']
                    ]);
                }
                if($model['supply_user_id'] > 0){
                    self::reduceBalanceByOrder($model['supply_user_id'], $model['order_id'], $model['rebate_money'], $model['order_no']);
                }
            }
        }
//        if(!empty($rebate) && $model['delivery_type']['value'] == 30){
//            ##查找获利人
//            $rebateUserId = $model['rebate_user_id'];
//            if($rebateUserId){
//                $rebateMoney = $model['rebate_money'];
//                ##返利给用户
//                self::addBalanceByOrder($rebateUserId, $model['order_id'], $rebateMoney, $model['order_no'], Scene::REBATE);
//                if($model['supply_user_id'] > 0){
//                    self::reduceBalanceByOrder($model['supply_user_id'], $model['order_id'], $rebateMoney, $model['order_no']);
//                }
//                ##添加返利日志
//                RebateLog::addLog([
//                    'user_id' => $rebateUserId,
//                    'supply_user_id' => $model['supply_user_id'],
//                    'buy_user_id' => $this['user_id'],
//                    'balance' => $rebateMoney,
//                    'order_id' => $model['order_id']
//                ]);
//            }
//        }

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
            'money' => -$balance,
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
    public static function getRebateUser2($userId, $supplyUserId){
        $userData = self::alias('u')
            ->join('user_grade ug','u.grade_id = ug.grade_id','LEFT')
            ->where(['u.user_id'=>$userId])
            ->field(['u.relation', 'ug.grade_type', 'u.grade_id', 'ug.weight'])
            ->find();
        if(!$userData['relation'])return [];
        $relation = explode('_',trim($userData['relation'],'_'));
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

    /**
     * 获取获利人 信息
     * @param $userId
     * @param $goodsId
     * @param $num
     * @param $supplyUserId
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRebateUser($userId, $goodsId, $num, $supplyUserId){
        ##获取用户最新的信息
        #获取商品信息
        $userInfo = self::getRecentUserInfo($userId, $goodsId, $num);
        ##获取最新等级
        $gradeInfo = Grade::getRecentGrade($userInfo['final_integral'],
            [
                'grade_id' => $userInfo['grade_id'],
                'weight' => $userInfo['grade']['weight'],
                'grade_type' => $userInfo['grade']['grade_type']
            ]);
        ##游客不返利
        if($gradeInfo['grade_type'] == GradeType::LOW){
            return [];
        }
        ##代理
        if($gradeInfo['grade_type'] == GradeType::HIGH){
            #VIP
            if($gradeInfo['weight'] == GradeSize::VIP){
                ##查找上级到供货人为止是否有VIP
                $rebateUserId = self::VIPGetRebate($userInfo['relation'], $supplyUserId);
                if(!$rebateUserId)return [];
                $rebateConf = RebateConfig::getConf()[RebateConfig::VIP][RebateConfig::VIP];
                $money = $rebateConf['rebate'] * $num;
                return [
                    [
                        'user_id' => $rebateUserId,
                        'money' => $money,
                        'remark' => $rebateConf['text'],
                        'num' => $num
                    ]
                ];
            }
            #总代
            elseif($gradeInfo['weight'] == GradeSize::AGENT){
                ##查找上级到供货人之前是否有总代
                $agentUserId = self::agentGetRebate($userInfo['relation'], $supplyUserId, 'agent');
                if($agentUserId){ ##有总代
                    $rtn = [];
                    $agentConf = RebateConfig::getConf()[RebateConfig::AGENT][RebateConfig::AGENT];
                    $agent_money = $agentConf['rebate'] * $num;
                    ##查看总代前是否有VIP
                    $vipUserId = self::agentGetRebate($userInfo['relation'], $agentUserId, 'vip');
                    if($vipUserId){ ##有VIP
                        $vipConf = RebateConfig::getConf()[RebateConfig::AGENT][RebateConfig::VIP];
                        $vip_money = $vipConf['rebate'] * $num;
                        $agent_money = $agent_money - $vip_money;
                        $rtn[] = [
                            'user_id' => $vipUserId,
                            'money' => $vip_money,
                            'text' => $vipConf['text'],
                            'num' => $num
                        ];
                    }
                    $rtn[] = [
                        'user_id' => $agentUserId,
                        'money' => $agent_money,
                        'text' => $agentConf['text'],
                        'num' => $num
                    ];
                    return $rtn;
                }else{ ##没有总代理
                    ##查找上级到供货人之前是否有VIP
                    $vipUserId = self::agentGetRebate($userInfo['relation'], $supplyUserId, 'vip');
                    if($vipUserId){ ##有vip
                        $vipConf = RebateConfig::getConf()[RebateConfig::AGENT][RebateConfig::VIP];
                        $vip_money = $vipConf['rebate'] * $num;
                        return [
                            [
                                'user_id' => $vipUserId,
                                'money' => $vip_money,
                                'text' => $vipConf['text'],
                                'num' => $num
                            ]
                        ];
                    }else{ ##无vip
                        return [];
                    }
                }
            }#战略董事
            elseif($gradeInfo['weight'] == GradeSize::STRATEGY){
                ##查找上级到发货人之间有没有战略董事(找两个)
                $strategyUser = self::strategyGetRebate($userInfo['relation'], $supplyUserId,'strategy');
                if(empty($strategyUser)){ ##没有战略董事
                    ##查找是否有总代
                    $agentUserId = self::strategyGetRebate($userInfo['relation'], $supplyUserId,'agent');
                    if($agentUserId){ ##有总代
                        $agentConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::AGENT];
                        $money = $agentConf['rebate'] * $num;
                        return [
                            [
                                'user_id' => $agentUserId,
                                'money' => $money,
                                'remark' => $agentConf['text'],
                                'num' => $num
                            ],
                        ];
                    }else{
                        return [];
                    }
                }else{ ##有战略董事
                    $rtn = [];
                    ##查找第一个战略懂事之前的第一个总代
                    $agentUserId = self::strategyGetRebate($userInfo['relation'], $strategyUser[0]['user_id'],'agent');
                    if(!$agentUserId){ ##没有总代
                        foreach($strategyUser as $k => $item){
                            if($k == 0){
                                $strategyConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::STRATEGY];
                                $money = $strategyConf['rebate'] * $num;
                            }else{
                                $strategyConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::STRATEGY_INDIRECT];
                                $money = $strategyConf['rebate'] * $num;
                            }
                            $rtn[] = [
                                'user_id' => $item['user_id'],
                                'money' => $money,
                                'remark' => $strategyConf['text'],
                                'num' => $num
                            ];
                        }
                    }else{ ##有总代
                        ##获取总代推战略懂事 低推高奖励
                        $agentConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::AGENT];
                        $agent_money = $agentConf['rebate'] * $num;
                        $rtn[] = [
                            'user_id' => $agentUserId,
                            'money' => $agent_money,
                            'remark' => $agentConf['text'],
                            'num' => $num
                        ];
                        foreach($strategyUser as $k => $item){
                            if($k == 0){
                                $strategyConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::STRATEGY];
                                $money = $strategyConf['rebate'] * $num - $agent_money;
                            }else{
                                $strategyConf = RebateConfig::getConf()[RebateConfig::STRATEGY][RebateConfig::STRATEGY_INDIRECT];
                                $money = $strategyConf['rebate'] * $num;
                            }
                            $rtn[] = [
                                'user_id' => $item['user_id'],
                                'money' => $money,
                                'remark' => $strategyConf['text'],
                                'num' => $num
                            ];
                        }
                    }
                    return $rtn;
                }
            }else{
                return [];
            }
        }
        ## 董事or合伙人
        if($gradeInfo['grade_type'] == GradeType::HIDE){
            ##查找上级到供货人为止是否有合伙人和董事
            $hideUserId = self::hideGetRebate($userInfo['relation']);
            if($hideUserId){ ##有董事和合伙人
                $hideConf = RebateConfig::getConf()[RebateConfig::DIRECTOR][RebateConfig::DIRECTOR];
                $money = $hideConf['rebate'] * $num;
                return [
                    [
                        'user_id' => $hideUserId,
                        'money' => $money,
                        'remark' => $hideConf['text'],
                        'num' => $num
                    ]
                ];
            }else{ ##没有董事和合伙人
                return [];
            }
        }
        return [];
    }

    /**
     * 获取用户代理关系
     * @param $userId
     * @return mixed
     */
    public static function getUserRelation($userId){
        return self::where(['user_id'=>$userId])->value('relation');
    }

    /**
     * 检查电话号码是否已被注册
     * @param $mobile
     * @return bool
     * @throws Exception
     */
    public static function checkExistMobile($mobile){
        $check = self::where(['mobile'=>$mobile])->count('user_id');
        return $check ? false : true;
    }

    /**
     * 获取VIP的获利人
     * @param $relation
     * @param $supply_user_id
     * @return array|int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function VIPGetRebate($relation, $supply_user_id){
        $relation = trim($relation,'_');
        if(!$relation)return 0;
        $relation = explode('_', $relation);
        if(!$relation[0])return 0;
        ##获取
        $filter = self::initFilter($relation, $supply_user_id);
        if(!$filter)return 0;
        $orderFilter = implode(',', $filter);
        ##VIP grade_id
        $grade_id = Grade::getGradeId(GradeSize::VIP);
        $rebateUser = self::where(['user_id'=>['IN', $filter], 'grade_id'=>$grade_id, 'is_delete'=>0])->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->find();
        if(!$rebateUser)return 0;
        return $rebateUser['user_id'];
    }

    /**
     * 获取总代【范围内】的第一个总代
     * @param $relation
     * @param $user_id
     * @param $type
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function agentGetRebate($relation, $user_id, $type){
        $relation = trim($relation,'_');
        if(!$relation)return 0;
        $relation = explode('_', $relation);
        if(!$relation[0])return 0;
        ##获取
        $filter = self::initFilter($relation, $user_id);
        if(!$filter)return 0;
        $orderFilter = implode(',', $filter);
        ##agent grade_id
        if($type == 'agent'){
            $grade_id = Grade::getGradeId(GradeSize::AGENT);
        }else{
            $grade_id = Grade::getGradeId(GradeSize::VIP);
        }
        $rebateUser = self::where(['user_id'=>['IN', $filter], 'grade_id'=>$grade_id, 'is_delete'=>0])->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->find();
        if(!$rebateUser)return 0;
        return $rebateUser['user_id'];
    }

    /**
     * 获取战略董事【范围内】的总代 or 战略懂事
     * @param $relation
     * @param $user_id
     * @param $type
     * @return array|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function strategyGetRebate($relation, $user_id, $type){
        $relation = trim($relation,'_');
        if(!$relation)return 0;
        $relation = explode('_', $relation);
        if(!$relation[0])return 0;
        ##获取
        $filter = self::initFilter($relation, $user_id);
        if(!$filter){
            return $type == 'agent' ? 0 : [];
        }
        $orderFilter = implode(',', $filter);

        if($type == 'agent'){
            ##agent grade_id
            $grade_id = Grade::getGradeId(GradeSize::AGENT);
        }else{
            ##strategy grade_id
            $grade_id = Grade::getGradeId(GradeSize::STRATEGY);
        }
        $where = [
            'user_id' => ['IN', $filter],
            'grade_id' => $grade_id,
            'is_delete' => 0
        ];

        if($type == 'strategy'){
            $rebateUser = self::where($where)->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->with(
                [
                    'grade' => function(Query $query){
                        $query->field(['grade_id', 'weight']);
                    }
                ]
            )->limit(2)->select();
            if(!$rebateUser)return [];
            return $rebateUser->toArray();
        }else{
            $rebateUser = self::where($where)->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->with(
                [
                    'grade' => function(Query $query){
                        $query->field(['grade_id', 'weight']);
                    }
                ]
            )->find();
            if(!$rebateUser)return 0;
            return $rebateUser['user_id'];
        }
    }

    /**
     * 获取董事or合伙人
     * @param $relation
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function hideGetRebate($relation){
        $relation = trim($relation,'_');
        if(!$relation)return 0;
        $relation = explode('_', $relation);
        if(!$relation[0])return 0;
        ##董事 grade_id and 合伙人 grade_id
        $grade_id_1 = Grade::getGradeId(GradeSize::DIRECTOR);
        $grade_id_2 = Grade::getGradeId(GradeSize::PARTNER);
        $where = [
            'user_id' => ['IN', $relation],
            'grade_id' => ['IN', [$grade_id_1, $grade_id_2]],
            'is_delete' => 0
        ];
        $orderFilter = implode(',', $relation);
        $rebateUser = self::where($where)->orderRaw("field(user_id," . $orderFilter . ")")->field(['user_id', 'grade_id'])->with(
            [
                'grade' => function(Query $query){
                    $query->field(['grade_id', 'weight']);
                }
            ]
        )->find();
        if(!$rebateUser)return 0;
        return $rebateUser['user_id'];
    }

    /**
     * 初始化查询条件
     * @param $relation
     * @param $supply_user_id
     * @return array
     */
    public static function initFilter($relation, $supply_user_id){
        if($supply_user_id > 0){
            $filter = [];
            foreach($relation as $rel){
                if($rel == $supply_user_id){
                    break;
                }
                $filter[] = $rel;
            }
        }else{
            $filter = $relation;
        }
        return $filter;
    }

}
