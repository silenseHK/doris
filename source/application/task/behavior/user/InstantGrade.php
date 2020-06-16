<?php


namespace app\task\behavior\user;

use app\task\model\User;
use app\task\model\User as UserModel;
use app\task\model\user\Grade as GradeModel;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use think\Cache;

class InstantGrade
{
    /**
     * @var int $userId
     * 用户id
     */
    private $userId;

    /**
     * 积分变更表id
     * @var int $integralLogId
     */
    private $integralLogId;

    /**
     * 执行函数
     * @param $options
     * @return bool
     * @throws \Exception
     */
    public function run($options)
    {
        $this->userId = $options['user_id'];
        $this->integralLogId = $options['integral_log_id'];

        $this->setUserGrade();
        return true;
    }

    /**
     * 设置用户的会员等级[升级]
     * @return array|bool|false
     * @throws \Exception
     */
    private function setUserGrade()
    {
        // 用户模型
        $UserModel = new UserModel;
        // 获取所有等级
        $list = GradeModel::getUpgradeUsableList(null, ['weight' => 'desc']);
        if ($list->isEmpty()) {
            return false;
        }
        ##获取用户信息
        $user = $UserModel->alias('u')->join('user_grade ug','u.grade_id = ug.grade_id','LEFT')->where(['u.user_id'=>$this->userId])->field(['u.integral', 'u.grade_id', 'ug.weight', 'ug.can_upgrade', 'u.invitation_user_id'])->find();

        if(!$user['can_upgrade'])return true;

        ##获取用户积分
        $integral = $user['integral'];
        foreach ($list as $k => $grade) {
            if($integral >= $grade['upgrade_integral'] && $user['weight'] < $grade['weight']){
                $data = [
                    'user_id' => $this->userId,
                    'old_grade_id' => $user['grade_id'],
                    'new_grade_id' => $grade['grade_id'],
                    'change_type' => ChangeTypeEnum::AUTO_UPGRADE,
                    'change_direction' => ChangeTypeEnum::LEVEL_UP,
                    'integral_log_id' => $this->integralLogId
                ];
                break;
            }
        }
        if(!isset($data)){
            $list = GradeModel::getUpgradeUsableList(null, ['weight' => 'asc']);
            foreach($list as $k => $grade){
                if($integral < $grade['upgrade_integral'] && $user['weight'] >= $grade['weight']){
                    $cur_grade = $list[$k-1]['grade_id'];
                    $data = [
                        'user_id' => $this->userId,
                        'old_grade_id' => $user['grade_id'],
                        'new_grade_id' => $cur_grade,
                        'change_type' => ChangeTypeEnum::AUTO_UPGRADE,
                        'change_direction' => ChangeTypeEnum::LEVEL_DOWN,
                        'integral_log_id' => $this->integralLogId
                    ];
                    break;
                }
            }
        }
        // 修改会员的等级
        return (isset($data) && !empty($data))? $UserModel->setUserGrade($data, $user) : false;
    }
}