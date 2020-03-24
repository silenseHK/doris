<?php

namespace app\api\model\user;

use app\common\model\user\Grade as GradeModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\api\model\user
 */
class Grade extends GradeModel
{

    /**
     * 获取等级列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGradeList(){
        return $this->where([
            'status' => 1,
            'is_delete' => 0,
            'is_show'=>1
        ])->field(['grade_id', 'name'])->order('weight','desc')->select();
    }

}