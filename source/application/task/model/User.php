<?php

namespace app\task\model;

use app\common\model\User as UserModel;
use app\task\model\user\GradeLog as GradeLogModel;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use think\Hook;

/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class User extends UserModel
{
    /**
     * 获取用户信息
     * @param $where
     * @param array $with
     * @return static|UserModel|null
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = [])
    {
        return parent::detail($where, $with);
    }

    /**
     * 查询满足会员等级升级条件的用户列表
     * @param $upgradeGrade
     * @param array $excludedUserIds
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUpgradeUserList($upgradeGrade, $excludedUserIds = [])
    {
        if (!empty($excludedUserIds)) {
            $this->where('user.user_id', 'not in', $excludedUserIds);
        }
        return $this->alias('user')
            ->field(['user.user_id', 'user.grade_id', 'user.integral'])
            ->join('user_grade grade', 'grade.grade_id = user.grade_id', 'LEFT')
//            ->where(function ($query) use ($upgradeGrade) {
//                $query->where('user.grade_id', '=', 0);
//                $query->whereOr('grade.weight', '<', $upgradeGrade['weight']);
//            })
            ->where('grade.weight', '<', $upgradeGrade['weight'])
            ->where('user.integral', '>=', $upgradeGrade['upgrade_integral'])
            ->where('user.is_delete', '=', 0)
            ->select();
    }

    public function getDownGradeUserList($upgradeGrade, $excludedUserIds = []){
        if (!empty($excludedUserIds)) {
            $this->where('user.user_id', 'not in', $excludedUserIds);
        }
        return $this->alias('user')
            ->field(['user.user_id', 'user.grade_id', 'user.integral'])
            ->join('user_grade grade', 'grade.grade_id = user.grade_id', 'LEFT')
//            ->where(function ($query) use ($upgradeGrade) {
//                $query->where('user.grade_id', '=', 0);
//                $query->whereOr('grade.weight', '<', $upgradeGrade['weight']);
//            })
            ->where('grade.weight', '>=', $upgradeGrade['weight'])
            ->where('user.integral', '>=', $upgradeGrade['upgrade_integral'])
            ->where('user.is_delete', '=', 0)
            ->select();
    }

    /**
     * 批量设置会员等级
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function setBatchGrade($data)
    {
        // 批量更新会员等级的数据
        $userData = [];
        // 批量更新会员等级变更记录的数据
        $logData = [];
        foreach ($data as $item) {
            $userData[] = [
                'user_id' => $item['user_id'],
                'grade_id' => $item['new_grade_id'],
            ];
            $logData[] = [
                'user_id' => $item['user_id'],
                'old_grade_id' => $item['old_grade_id'],
                'new_grade_id' => $item['new_grade_id'],
                'change_type' => ChangeTypeEnum::AUTO_UPGRADE,
                'balance_integral' => $item['balance_integral'],
                'change_integral' => $item['change_integral'],
                'change_direction' => $item['change_direction'],
            ];
        }
        // 批量更新会员等级
        $status = $this->isUpdate()->saveAll($userData);
        // 批量更新会员等级变更记录
        (new GradeLogModel)->records($logData);
        return $status;
    }

    /**
     * 设置单用户等级
     * @param $data
     * @param $user
     * @return false|int
     */
    public function setUserGrade($data, $user){
        ##更新会员等级
        $res = $this->isUpdate()->save(['user_id'=>$data['user_id'], 'grade_id'=>$data['new_grade_id']]);
        ##插入变更记录
        (new GradeLogModel())->recordsOne($data);
        ##推荐升级
        $params = [
            'user_id' => $user['invitation_user_id']
        ];
        Hook::listen('agent_instant_grade',$params);
        return $res;
    }

}
