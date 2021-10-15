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
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGradeList(){
        $list = $this->where([
            'status' => 1,
            'is_delete' => 0
        ])->field(['grade_id', 'name'])->order('weight','desc')->select()->toArray();
        $all[] = [
            'grade_id' => 0,
            'name' => '全部'
        ];
        $list = array_merge($all, $list);
        return $list;
    }

    /**
     * 获取下一等级的信息
     * @param $weight
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getNextGradeInfo($weight){
        return self::where(['weight'=>['GT', $weight], 'can_upgrade'=>1, 'is_delete'=>0])->order('weight','asc')->field(['name', 'upgrade_integral', 'grade_id'])->find();
    }

}