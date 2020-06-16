<?php

namespace app\store\model\user;

use app\common\enum\user\grade\GradeType as GradeTypeEnum;
use app\common\enum\user\grade\Rebate as RebateEnum;
use app\common\model\user\Grade as GradeModel;

use app\store\model\User as UserModel;
use think\Validate;

/**
 * 用户会员等级模型
 * Class Grade
 * @package app\store\model\user
 */
class Grade extends GradeModel
{
    /**
     * 获取列表记录
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['weight' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['name'] = str_filter($data['name']);
        $data['weight'] = intval($data['weight']);
        $data['upgrade_integral'] = intval($data['upgrade_integral']);
        $data['status'] = intval($data['status']);
        $data['grade_type'] = intval($data['grade_type']);
        $data['is_rebate'] = intval($data['is_rebate']);
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data, 'edit')) {
            return false;
        }
        $data['name'] = str_filter($data['name']);
        $data['weight'] = intval($data['weight']);
        $data['upgrade_integral'] = intval($data['upgrade_integral']);
        $data['status'] = intval($data['status']);
        $data['grade_type'] = intval($data['grade_type']);
        $data['is_rebate'] = intval($data['is_rebate']);
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        // 判断该等级下是否存在会员
        if (UserModel::checkExistByGradeId($this['grade_id'])) {
            $this->error = '该会员等级下存在用户，不允许删除';
            return false;
        }
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 表单验证
     * @param $data
     * @param string $scene
     * @return bool
     */
    private function validateForm($data, $scene = 'add')
    {
        #数据验证
        $rule = [
            'name|会员名称' => 'require|max:10|min:2|unique:user_grade,name',
            'weight|等级权重' => 'require|number|>=:1',
            'upgrade_integral|升级条件' => 'require|number|>=:0',
        ];
        if($scene === 'edit'){
            $rule['name|会员名称'] = "require|max:10|min:2|unique:user_grade,name,{$this['grade_id']},grade_id";
        }
        $validate = new Validate($rule);
        $res = $validate->check($data);
        if(!$res){
            $this->error = $validate->getError();
            return false;
        }
        if ($scene === 'add') {
            // 需要判断等级权重是否已存在
            if (self::checkExistByWeight($data['weight'])) {
                $this->error = '等级权重已存在';
                return false;
            }
            //  判断低等级的升级条件低于高等级
            if (!self::checkUpgradeByWeight($data['weight'], $data['upgrade_integral'])){
                $this->error = '高等级的会员升级条件必须高于低等级会员升级条件';
                return false;
            }
        } elseif ($scene === 'edit') {
            // 需要判断等级权重是否已存在
            if (self::checkExistByWeight($data['weight'], $this['grade_id'])) {
                $this->error = '等级权重已存在';
                return false;
            }
            // 判断低等级的升级条件低于高等级
            if (!self::checkUpgradeByWeight($data['weight'], $data['upgrade_integral'], $this['grade_id'])){
                $this->error = '高等级的会员升级条件必须高于低等级会员升级条件';
                return false;
            }
        }
        return true;
    }

    /**
     * 获取会员等级信息
     * @param $grade_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGradeInfo($grade_id){
        return self::where(['grade_id'=>$grade_id])->field(['grade_id', 'weight', 'upgrade_integral'])->find();
    }

    /**
     * 获取器 重组等级类型数据
     * @param $value
     * @return mixed
     */
    public function getGradeTypeAttr($value){
        return GradeTypeEnum::data()[$value];
    }

    /**
     * 获取器 重组返利数据
     * @param $value
     * @return mixed
     */
    public function getIsRebateAttr($value){
        return RebateEnum::data()[$value];
    }

    /**
     * 可选择等级列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGradeList(){
        return self::where(['is_delete'=>0, 'status'=>1])->field(['grade_id', 'name'])->order('weight','asc')->select();
    }

}