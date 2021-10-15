<?php


namespace app\task\behavior\user;


use app\common\enum\user\grade\GradeSize;
use app\common\model\User;
use app\common\model\user\Grade;
use app\common\model\user\GradeLog;
use app\common\model\user\IntegralLog;
use think\Db;
use think\Exception;

/**
 * 代理推荐升级
 * Class AgentInstantGrade
 * @package app\task\behavior\user
 */

class AgentInstantGrade
{

    /**
     * @var int $userId
     * 用户id
     */
    private $userId;

    protected $rule = [
        GradeSize::VIP => [
            'invite_grade_weight' => GradeSize::VIP,
            'num' => '4',
            'limit_num' => 0,
            'next_grade_id' =>  GradeSize::AGENT
        ],
        GradeSize::AGENT => [
            'invite_grade_weight' => GradeSize::AGENT,
            'num' => '4',
            'limit_num' => 0,
            'next_grade_id' => GradeSize::STRATEGY
        ],
        GradeSize::STRATEGY => [
            [
                'invite_grade_weight' => GradeSize::STRATEGY,
                'num' => '7',
                'limit_num' => 30,
                'next_grade_id' => GradeSize::PARTNER
            ],
            [
                'invite_grade_weight' => GradeSize::STRATEGY,
                'num' => '4',
                'limit_num' => 70,
                'next_grade_id' => GradeSize::DIRECTOR
            ],
        ],
        GradeSize::DIRECTOR => [
            'invite_grade_weight' => GradeSize::DIRECTOR,
            'num' => '2',
            'limit_num' => 30,
            'next_grade_id' => GradeSize::PARTNER
        ]
    ];

    public function run($options){
        $this->userId = $options['user_id'];
//        $this->updateAgentGrade();
        return true;
    }

    /**
     * 推荐代理升级
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    private function updateAgentGrade(){
        ##获取用户信息
        $user = User::get(['user_id'=>$this->userId],['grade']);
        if(!isset($this->rule[$user['grade']['weight']]))return true;
        $rule = $this->rule[$user['grade']['weight']];
        if(count($rule) < 4){
            $invite_grade_weight = $rule[0]['invite_grade_weight'];
        }else{
            $invite_grade_weight = $rule['invite_grade_weight'];
            $rule = [$rule];
        }
        $invite_grade_id = Grade::getGradeId($invite_grade_weight);
        ##
        $num = $this->countMemberNum($invite_grade_id);
        foreach($rule as $key => $item){
            if($num >= $item['num']){ ##满足条件
                $agent_grade_id = Grade::getGradeId($item['next_grade_id']);
                if($item['limit_num'] > 0){
                    $agent_num = User::where(['grade_id'=>$agent_grade_id,'is_delete'=>0])->count();
                    if($agent_num >= $item['limit_num']){
                        continue;
                    }
                }
                $this->updateGrade($agent_grade_id, $user);
                continue;
            }
        }
        return true;
    }

    /**
     * 获取满足规则的用户数
     * @param $grade_id
     * @return int|string
     * @throws \think\Exception
     */
    public function countMemberNum($grade_id){
         return User::where(['grade_id'=>$grade_id,'invitation_user_id'=>$this->userId,'is_delete'=>0])->count();
    }

    /**
     * 更新等级
     * @param $grade_id
     * @param $user
     * @return bool
     * @throws \think\exception\DbException
     */
    public function updateGrade($grade_id, $user){
        $grade_info = Grade::get(['grade_id'=>$grade_id]);
        Db::startTrans();
        try{
            ##用户升级
            $res = User::where(['user_id'=>$this->userId])->update(['grade_id' => $grade_id, 'integral' => $grade_info['upgrade_integral']]);
            if($res === false)throw new Exception('用户升级失败');
            ##积分变化记录
            $integral_data = [
                'user_id' => $this->userId,
                'balance_integral' => $user['integral'],
                'change_integral' => $grade_info['upgrade_integral'] - $user['integral'],
                'change_direction' => 10,
                'change_type' => 30
            ];
            $integral_model = new IntegralLog();
            $res = $integral_model->isUpdate(false)->save($integral_data);
            if($res === false)throw new Exception('积分变化记录插入失败');
            ##升级记录
            $data = [
                'old_grade_id' => $user['grade_id'],
                'change_type' => 30,
                'remark' => '推荐升级',
                'change_direction' => 10,
                'integral_log_id' => $integral_model->getLastInsID()
            ];
            $res = (new GradeLog())->recordsOne($data);
            if($res === false)throw new Exception('升级日志插入失败');
            Db::commit();
            $this->run(['user_id'=>$user['invitation_user_id']]);
            return true;
        }catch(Exception $e){
            Db::rollback();
            log_write("{$e->getMessage()}",'error');
            return false;
        }
    }

}