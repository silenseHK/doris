<?php


namespace app\common\model\college;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class LessonCate extends BaseModel
{

    protected $name = 'college_lesson_cate';

    protected $pk = 'lesson_cate_id';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $insert = ['level'];

    /**
     * 设置分类等级
     * @param $value
     * @param $data
     * @return int
     */
    public function setLevelAttr($value, $data){
        return $this->getLevel($data['pid']);
    }

    /**
     * 生成level
     * @param $pid
     * @return int
     */
    protected function getLevel($pid){
        return $pid > 0 ? 2 : 1;
    }

    /**
     * 获取分类信息
     * @param $cate_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCateInfo($cate_id){
        return self::where(['lesson_cate_id'=>$cate_id])->field(['lesson_cate_id', 'title', 'pid'])->find();
    }

}