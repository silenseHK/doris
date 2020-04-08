<?php

namespace app\store\model;

use app\common\enum\user\balanceLog\Scene;
use app\common\model\User as UserModel;

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
use think\Exception;
use think\Hook;

/**
 * 用户模型
 * Class User
 * @package app\store\model
 */
class User extends UserModel
{
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
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($nickName = '', $gender = -1, $grade = null)
    {
        // 检索：微信昵称
        !empty($nickName) && $this->where('nickName', 'like', "%$nickName%");
        // 检索：性别
        if ($gender !== '' && $gender > -1) {
            $this->where('gender', '=', (int)$gender);
        }
        // 检索：会员等级
        $grade > 0 && $this->where('grade_id', '=', (int)$grade);
        // 获取用户列表
        return $this->with(['grade'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
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
            return $this->rechargeToPoints($data['points']);
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
        ## 变更用户商品库存
        $res = UserGoodsStock::updateUserGoodsStock($this['user_id'], $goodsId, $goodsSkuId, $finalStock, $diffStock ,'ADMIN', $data['remark']);
        if($res !== true){
            $this->error = $res;
            return false;
        }
        return true;
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
        self::update(['balance'=>['inc', $money], 'freeze_money'=>['dec', $money]], compact('user_id'));
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

}
