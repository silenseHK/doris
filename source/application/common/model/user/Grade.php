<?php

namespace app\common\model\user;

use think\Hook;
use app\common\model\BaseModel;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\common\model\user
 */
class Grade extends BaseModel
{
    protected $name = 'user_grade';

    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听订单处理事件
//        $static = new static;
//        Hook::listen('user_grade', $static);
    }

    /**
     * 获取器：升级条件
     * @param $json
     * @return mixed
     */
    public function getUpgradeAttr($json)
    {
        return json_decode($json, true);
    }

    /**
     * 获取器：等级权益
     * @param $json
     * @return mixed
     */
    public function getEquityAttr($json)
    {
        return json_decode($json, true);
    }

    /**
     * 修改器：升级条件
     * @param $data
     * @return mixed
     */
    public function setUpgradeAttr($data)
    {
        return json_encode($data);
    }

    /**
     * 修改器：等级权益
     * @param $data
     * @return mixed
     */
    public function setEquityAttr($data)
    {
        return json_encode($data);
    }

    /**
     * 会员等级详情
     * @param $grade_id
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($grade_id, $with = [])
    {
        return static::get($grade_id, $with);
    }

    /**
     * 获取可用的会员等级列表
     * @param null $wxappId
     * @param array $order 排序规则
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getUsableList($wxappId = null, $order = ['weight' => 'asc'])
    {
        $model = new static;
        $wxappId = $wxappId ? $wxappId : $model::$wxapp_id;
        return $model->where('status', '=', '1')
            ->where('is_delete', '=', '0')
            ->where('wxapp_id', '=', $wxappId)
            ->order($order)
            ->select();
    }

    /**
     * 验证等级权重是否存在
     * @param int $weight 验证的权重
     * @param int $gradeId 自身的等级ID
     * @return bool
     */
    public static function checkExistByWeight($weight, $gradeId = 0)
    {
        $model = new static;
        $gradeId > 0 && $model->where('grade_id', '<>', (int)$gradeId);
        return $model->where('weight', '=', (int)$weight)
            ->value('grade_id');
    }

    /**
     * 验证高等级的条件是否大于低等级的条件
     * @param int $weight 验证权重
     * @param int $upgrade 升级条件
     * @param int $grade_id 自身的等级id
     * @return bool
     */
    public static function checkUpgradeByWeight($weight, $upgrade, $grade_id=0)
    {
        $model = new static;
        ##高等级
        $where = [
            'weight' => ['>', $weight]
        ];
        if($grade_id)$where['grade_id'] = ['neq', $grade_id];
        $data = $model->where($where)->order('weight','asc')->value('upgrade_integral');
        if($data && $data < $upgrade)return false;
        ##低等级
        $where2 = [
            'weight' => ['<', $weight]
        ];
        if($grade_id)$where2['grade_id'] = ['<>', $grade_id];
        $data = $model->where($where2)->order('weight','desc')->value('upgrade_integral');
        if($data && $data > $upgrade)return false;

        return true;
    }

    /**
     * 获取用户当前的等级
     * @param $integral  *最新的积分
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getRecentGrade($integral){
        $gradeInfo = self::where(['upgrade_integral'=>['ELT', $integral], 'is_delete'=>0, 'status'=>1])->order('weight', 'desc')->field(['grade_id', 'weight'])->find();
        if(!$gradeInfo)$gradeInfo = self::getLowestGrade();
        return $gradeInfo;
    }

    /**
     * 获取供货用户的等级id
     * @param $weight
     * @return mixed
     */
    public static function getApplyGrade($weight){
        return self::where(['weight' => ['GT', $weight], 'is_delete'=>0, 'status'=>1])->order('weight','asc')->column('grade_id');
    }

    /**
     * 获取最低会员等级信息
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getLowestGrade(){
        return self::where(['is_delete'=>0, 'status'=>1])->order('weight', 'asc')->field(['grade_id', 'weight'])->find();
    }

    /**
     * 获取最高会员等级信息
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getHighestGrade(){
        return self::where(['is_delete'=>0, 'status'=>1])->order('weight','desc')->field(['grade_id', 'weight'])->find();
    }

    /**
     * 获取返利用户的等级id
     * @param $weight
     * @param int $gradeType
     * @return array
     */
    public static function getRebateGrade($weight, $gradeType=10){
        return self::where([
                'weight'=>['GT', $weight],
                'grade_type' => $gradeType,
                'is_delete' => 0,
                'status' => 1
            ])
            ->column('grade_id');
    }

}