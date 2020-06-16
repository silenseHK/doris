<?php


namespace app\api\model\college;

use app\api\validate\college\LessonValid;
use app\common\model\college\CollegeWatchRecord as CollegeWatchRecordModel;
use think\db\Query;

class CollegeWatchRecord extends CollegeWatchRecordModel
{

    /**
     * 检查是否已观看过
     * @param $where
     * @return int|string
     * @throws \think\Exception
     */
    public static function checkWatched($where){
        return self::where($where)->count();
    }

    /**
     * 新增观看记录
     * @param $data
     * @return false|int
     */
    public static function add($data){
        return (new self)->isUpdate(false)->save($data);
    }

    /**
     * 更新观看时间
     * @param $where
     * @return CollegeWatchRecord
     */
    public static function edit($where){
        return self::update(['update_time'=>time()], $where);
    }

    /**
     * 用户观看记录
     * @param $user
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function watchRecord($user){
        ##验证
        $validate = new LessonValid();
        if(!$validate->scene('watch_record')->check(input()))
            return $this->setError($validate->getError());
        ##参数
        $size = input('get.size',6,'intval');
        ##数据
        $list = $this
            ->where([
                'user_id' => $user['user_id']
            ])
            ->with(
                [
                    'collegeClass' => function(Query $query){
                        $query->with(
                            [
                                'lesson' => function(Query $query){
                                    $query
                                        ->with(
                                            [
                                                'cate' => function(Query $query){
                                                    $query->field(['lesson_cate_id', 'title']);
                                                }
                                            ]
                                        )
                                        ->field(['lesson_id', 'cate_id', 'lesson_type']);
                                },
                                'image' => function(Query $query){
                                    $query->field(['file_id', 'storage', 'file_url', 'file_name']);
                                }
                            ]
                        )
                        ->field(['class_id', 'lesson_id', 'cover', 'title', 'desc', 'create_time', 'watch_num']);
                    }
                ]
            )
            ->order('update_time','desc')
            ->paginate($size,false,['query'=>\request()->request()]);
        return compact('list');
    }

}