<?php


namespace app\api\model\college;

use app\common\model\college\LessonCate as LessonCateModel;

class LessonCate extends LessonCateModel
{

    /**
     * 课程分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getLists(){
        $list = self::where(['is_show'=>1])->field(['lesson_cate_id', 'title', 'sort', 'is_show', 'create_time', 'pid'])->order('sort','asc')->select()->toArray();
        $list = cateTree($list, 0,'lesson_cate_id');
        return $list;
    }

}