<?php


namespace app\api\model\college;

use app\api\validate\college\ClassValid;
use app\common\model\college\CollegeClass as CollegeClassModel;
use think\db\Query;
use think\Exception;

class CollegeClass extends CollegeClassModel
{

    /**
     * 统计课程课时数
     * @param $lesson_id
     * @return int|string
     * @throws Exception
     */
    public static function countLessonClassNum($lesson_id){
        return self::where(['lesson_id'=>$lesson_id])->count();
    }

    /**
     * 课时列表
     * @param $lesson_id
     * @param int $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getClassList($lesson_id, $page=1){
        $list = $this
                ->where(['lesson_id'=>$lesson_id, 'status'=>1])
                ->with(
                    [
                        'image' => function(Query $query){
                            $query->field(['file_id', 'storage', 'file_name']);
                        },
                        'lesson' => function(Query $query){
                            $query->field(['lesson_id', 'lesson_type', 'cate_id'])->with(['cate'=>function(Query $query){$query->field(['lesson_cate_id', 'title']);}]);
                        }
                    ]
                )
                ->field(['class_id', 'cover', 'title', 'desc', 'watch_num', 'create_time'])
                ->page($page, 10)
                ->select();
        return $list;
    }

    /**
     * 课时详情
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getDetail($user){
        ##验证
        $valid = new ClassValid();
        if(!$valid->scene('class_detail')->check(input()))throw new Exception($valid->getError());
        ##参数
        $class_id = input('get.class_id',0,'intval');
        $info = self::get(
            [
                'class_id'=>$class_id
            ],
            [
                'lesson' => function(Query $query){
                    $query
                        ->field(['lesson_id', 'lesson_type', 'lecturer_id', 'cate_id'])
                        ->with(
                            [
                                'lecturer' => function(Query $query){
                                    $query
                                        ->field(['lecturer_id', 'name', 'avatar', 'label'])
                                        ->with(
                                            [
                                                'image' => function(Query $query){
                                                    $query->field(['file_id', 'storage', 'file_name']);
                                                }
                                            ]
                                        );
                                },
                                'cate' => function(Query $query){
                                    $query->field(['lesson_cate_id', 'title']);
                                }
                            ]
                        );
                },
                'liveRoom',
                'image' => function(Query $query){
                    $query->field(['file_id', 'storage', 'file_name']);
                }
            ]
        );

        if(!$info)throw new Exception('课程数据不存在');
        if($info['status'] != 1)throw new Exception('课程已下架');
        ##判断是否关注讲师
        $is_lecturer_collect = 0;
        if($user){
            $is_lecturer_collect = LecturerCollect::checkCollect($user['user_id'], $info['lesson']['lecturer_id']);
        }
        $info['is_lecturer_collect'] = $is_lecturer_collect;
        ##判断是否关注课程
        $is_lesson_collect = 0;
        if($user){
            $is_lesson_collect = LessonCollect::checkCollect($user['user_id'], $info['lesson_id']);
        }
        $info['is_lesson_collect'] = $is_lesson_collect;
        return compact('info');
    }

    /**
     * 观看课时
     * @param $class_id
     * @param $lesson_id
     * @throws Exception
     */
    public static function watch($class_id, $lesson_id){
        self::watchClass($class_id);
        Lesson::watchLesson($lesson_id);
    }

    /**
     * 增加课时观看数量
     * @param $class_id
     * @return int|true
     * @throws Exception
     */
    public static function watchClass($class_id){
        return self::where(['class_id'=>$class_id])->setInc('watch_num',1);
    }

}