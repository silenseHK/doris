<?php


namespace app\common\service;

use app\common\enum\user\grade\GradeSize;
use app\common\model\Goods;
use app\common\model\GoodsGrade;
use app\common\model\User;
use app\common\model\user\ManageRewardLog;
use app\common\model\UserGoodsStock;
use think\Cache;
use think\Db;
use app\common\model\user\Grade;
use app\common\model\UserGoodsStockLog;
use think\Exception;

class ManageReward
{

    protected $user; ##用户model

    protected $goods_ids; ##需要计算奖励的商品

    protected $reward_setting; ##奖励的规格

    protected $grade_id; ##战略董事的等级id

    protected $strategy_ids; ##战略董事

    protected $strategy_list; ##战略董事列表

    protected $error = '';

    protected $num_data = []; ##进货数据

    protected $goods_price;

    protected $empty_info = [
        'money' => 0,
        'need_buy' => false
    ];

    protected $person_reward = 0;

    protected $person_stock_info = [];

    public function __construct()
    {
        $this->goods_ids = $this->goods();
        $this->reward_setting = $this->setting();
        $this->grade_id = $this->grade();
        $this->goods_price = $this->goodsPrice();
        $this->user = new User();
    }

    /**
     * 统计单用户本月团队管理奖
     * @param $userInfo
     * @return array
     * @throws Exception
     */
    public function personCountReward($userInfo){
        ##获取用户信息
        if($userInfo['grade_id'] != $this->grade_id)return $this->empty_info;
        ##获取下级的总进货量
        if(!$this->goods_ids)return $this->empty_info;
        ##获取下级中的战略
        $user_ids = $this->user->where(['relation'=>['LIKE', "%-{$userInfo['user_id']}-%"], 'grade_id'=>$this->grade_id])->column('user_id');
        $start_time = get_month_start_timestamp();
        $end_time = get_month_end_timestamp();
        $model = new UserGoodsStockLog();
        foreach($this->goods_ids as $goods_id){
            $child_stock = empty($user_ids)? 0 : $model->getUsersStock($goods_id, $user_ids, $start_time, $end_time);
            $self_stock = $model->getBuyStock($goods_id, $userInfo['user_id'], $start_time, $end_time);
            $total_money = ($child_stock + $self_stock) * $this->goods_price[$goods_id];
            $child_money = $child_stock * $this->goods_price[$goods_id];
            $reward = $total_money - $child_money;
            $this->person_stock_info[$goods_id] = compact('total_money','child_money','reward','child_stock','self_stock');
            $this->person_reward += $reward;
        }
        ##判断是否需要补充库存
        $is_negative_stock = UserGoodsStock::countNegativeStock($userInfo['user_id']) > 0;
        return [
            'money' => $this->person_reward,
            'need_buy' => $is_negative_stock
        ];
    }

    /**
     * 计算团队管理数据
     * @return array
     */
    public function countReward(){
        ##获取战略董事
        $this->strategy();
        if(!$this->goods_ids)return ['code'=>100, 'msg'=>'没有团队管理商品'];
        if(!$this->strategy_ids)return ['code'=>100, 'msg'=>'没有战略董事'];
        ##组装代理关系
        $this->cardingStrategy();
        ##获取进货数据
        $this->countBuyStock();
        ##获取进货金额以及奖励
        $this->reward();
        return ['code'=>1, 'msg'=>'success'];
    }

    /**
     * 获取数据
     * @return array
     */
    public function getNumData(){
        return $this->num_data;
    }

    /**
     * 插入记录
     * @throws \Exception
     */
    public function insertRewardLog(){
        $timestamp = Cache::get('manage_reward_refresh');
        if($timestamp > time() - 10 * 60){
            $this->error='操作频繁';
            return;
        }
        Cache::set('manage_reward_refresh',time());
        $date = date('Y-m');
        $rewards_data = [];
        ##插入团队管理奖
        foreach($this->num_data as $user_id => $rewards){
            foreach($rewards as $goods_id => $reward){
                if($reward['self_reward'] > 0){
                    $rewards_data[] = [
                        'user_id' => $user_id,
                        'goods_id' => $goods_id,
                        'self_money' => $reward['self_money'],
                        'total_reward' => $reward['total_reward'],
                        'child_reward' => $reward['child_reward'],
                        'self_reward' => $reward['self_reward'],
                        'total_money' => $reward['total_money'],
                        'date' => $date,
                    ];
                }
            }
        }
        $model = new ManageRewardLog();
        if($rewards_data){
            $model->startTrans();
            try{
                ##删除以前的记录
                $res = $model->where(['date'=>$date])->delete();
                if($res === false)throw new  \Exception('操作失败');
                $res = $model->isUpdate(false)->saveAll($rewards_data);
                if($res === false)throw new \Exception('操作失败-');
                Db::commit();
            }catch(Exception $e){
                $this->error = $e->getMessage();
                Db::rollback();
            }
        }
    }

    protected function goods(){
        $model = new Goods();
        return $model->where(['is_manage_reward'=>1, 'sale_type'=>1])->column('goods_id');
    }

    /**
     * 每个商品的价格
     */
    protected function goodsPrice(){
        $price_data = [];
        foreach($this->goods_ids as $id){
            $price_data[$id] = GoodsGrade::getGoodsPrice($this->grade_id, $id);
        }
        return $price_data;
    }

    protected function setting(){
        return Db::name('manage_reward')->order('money','desc')->field(['money', 'reward_percent'])->select();
    }

    protected function grade(){
        $model = new Grade();
        return $model->where(['weight'=>GradeSize::STRATEGY, 'is_delete'=>0])->value('grade_id');
    }

    protected function strategy(){
        $strategy_list = $this->user->where(['grade_id'=>$this->grade_id])->field(['user_id', 'relation'])->select()->toArray();
        $this->strategy_ids = array_column($strategy_list, 'user_id');
        $this->strategy_list = $strategy_list;
    }

    /**
     * 获取进货情况
     */
    protected function countBuyStock(){
        $start_time = get_month_start_timestamp();
        $end_time = get_month_end_timestamp();
        $model = new UserGoodsStockLog();
        foreach($this->goods_ids as $goods_id){
            foreach($this->strategy_list as $strategy){
                $strategy_id = $strategy['user_id'];
                ##获取进货量
                if(!isset($this->num_data[$strategy_id]))$this->num_data[$strategy_id] = [];
                if(!isset($this->num_data[$strategy_id][$goods_id]))$this->num_data[$strategy_id][$goods_id] = [];
                $num = $model->getBuyStock($goods_id, $strategy_id, $start_time, $end_time);
                $this->num_data[$strategy_id][$goods_id]['self'] = $num;
                if(!isset($this->num_data[$strategy_id][$goods_id]['total']))$this->num_data[$strategy_id][$goods_id]['total'] = 0;
                $this->num_data[$strategy_id][$goods_id]['total'] += $num;
                if(isset($strategy['parent']) && $strategy['parent']){ ##给上级增加
                    foreach($strategy['parent'] as $pa){
                        if(!isset($this->num_data[$pa]))$this->num_data[$pa] = [];
                        if(!isset($this->num_data[$pa][$goods_id]))$this->num_data[$pa][$goods_id] = [];
                        if(!isset($this->num_data[$pa][$goods_id]['total']))$this->num_data[$pa][$goods_id]['total'] = 0;
                        $this->num_data[$pa][$goods_id]['total'] += $num;
                    }
                }
            }
        }
    }

    /**
     * 获取奖励金额
     */
    protected function reward(){
        foreach($this->num_data as $strategy_id => $item){
            foreach($item as $goods_id => $val){
                ##总共金额
                $total_money = $val['total'] * $this->goods_price[$goods_id];
                $self_money = $val['self'] * $this->goods_price[$goods_id];
                $this->num_data[$strategy_id][$goods_id]['total_money'] = $total_money;
                $this->num_data[$strategy_id][$goods_id]['self_money'] = $self_money;
                $total_reward = $this->getRewardMoney($total_money);
                $child_reward = $this->getRewardMoney($total_money - $self_money);
                $self_reward = $total_reward - $child_reward;
                $this->num_data[$strategy_id][$goods_id]['total_reward'] = $total_reward;
                $this->num_data[$strategy_id][$goods_id]['child_reward'] = $child_reward;
                $this->num_data[$strategy_id][$goods_id]['self_reward'] = $self_reward;
            }
        }
    }

    /**
     * 组装代理关系
     */
    protected function cardingStrategy(){
        $list1 = $list2 = $this->strategy_list;
        foreach($list1 as $key => $item){
            foreach($list2 as $strategy){
                $relation = trim($item['relation'],'-');
                $relation = explode('-',$relation);
                if(in_array($strategy['user_id'],$relation)){ ##当前董事是循环董事的下级
                    if(!isset($list1[$key]['parent']))$list1[$key]['parent'] = [];
                    $list1[$key]['parent'][] = $strategy['user_id'];
                }
            }
        }
        $this->strategy_list = $list1;
    }

    /**
     * 获取错误信息
     * @return bool
     */
    public function getError(){
        return $this->error ? : false;
    }

    /**
     * 获取奖励金额
     * @param $money
     * @return float|int
     */
    protected function getRewardMoney($money){
        return ($money * $this->getCurSetting($money)) / 100;
    }

    /**
     * 获取奖励设置
     * @param $money
     * @return float|int
     */
    protected function getCurSetting($money){
        foreach($this->reward_setting as $item){
            if($money >= $item['money']){
                return $item['reward_percent'];
                break;
            }
        }
        return 0;
    }

}