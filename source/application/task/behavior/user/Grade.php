<?php

namespace app\task\behavior\user;

use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use think\Cache;
use app\task\model\User as UserModel;
use app\task\model\user\Grade as GradeModel;

class Grade
{
    /* @var GradeModel $model */
    private $model;

    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \Exception
     */
    public function run($model)
    {
        if (!$model instanceof GradeModel) {
            return new GradeModel and false;
        }
        $this->model = $model;
        if (!$model::$wxapp_id) {
            return false;
        }
        $cacheKey = "__task_space__[user/Grade]__{$model::$wxapp_id}";
        if (!Cache::has($cacheKey)) {
            // 设置用户的会员等级
            $this->setUserGrade();
            Cache::set($cacheKey, time(), 60 * 10);
        }
        return true;
    }

    /**
     * 设置用户的会员等级
     * @return array|bool|false
     * @throws \Exception
     */
    private function setUserGrade()
    {
        // 用户模型
        $UserModel = new UserModel;
        // 获取所有等级
        $list = GradeModel::getUsableList(null, ['weight' => 'desc']);
        if ($list->isEmpty()) {
            return false;
        }
        // 遍历等级，根据升级条件 查询满足积分升级的用户列表，并且他的等级小于该等级
        $data = $downData = [];
        foreach ($list as $grade) {
            $userList = $UserModel->getUpgradeUserList($grade, array_merge(array_keys($data), array_keys($downData)));
            foreach ($userList as $user) {
                if (!isset($data[$user['user_id']])) {
                    $data[$user['user_id']] = [
                        'user_id' => $user['user_id'],
                        'old_grade_id' => $user['grade_id'],
                        'new_grade_id' => $grade['grade_id'],
                        'change_type' => ChangeTypeEnum::ADMIN_USER, //后台的会员等级条件出现变动
                        'change_direction' => ChangeTypeEnum::LEVEL_UP,
                        'integral_log_id' => 0,
                        'remark' => '自动检测升级,会员等级出现变动引起'
                    ];
                }
            }
            $downUserList = $UserModel->getDownGradeUserList($grade, array_merge(array_keys($data), array_keys($downData)));
        }

        

        // 批量修改会员的等级
        return $UserModel->setBatchGrade($data);
    }

}