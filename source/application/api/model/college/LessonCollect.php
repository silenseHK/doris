<?php


namespace app\api\model\college;

use app\api\validate\college\LessonValid;
use app\common\model\college\LessonCollect as LessonCollectModel;
use think\db\Query;
use think\Exception;


class LessonCollect extends LessonCollectModel
{

    protected $user_id;

    protected $lesson_id;

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new LessonValid();
    }

    /**
     * 收藏、取消收藏课程
     * @param $user
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function lessonCollect($user){
        $this->user_id = $user['user_id'];
        ##验证
        if(!$this->valid->scene('collect')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $lesson_id = input('post.lesson_id',0,'intval');
        $lesson = Lesson::get(['lesson_id'=>$lesson_id]);
        $type = input('post.type',1,'intval');
        ##检查课程信息
        if(!$lesson)
            return $this->setError('课程信息不存在或已下架');
        $this->lesson_id = $lesson_id;
        ##执行操作
        switch($type){
            case 1: ##收藏
                return $this->collect();
                break;
            case 2: ##取消收藏
                return $this->cancel();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 收藏
     * @param $user
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    protected function collect(){
        $user_id = $this->user_id;
        $lesson_id = $this->lesson_id;
        ##查看是否已收藏
        if(self::checkCollect($user_id, $lesson_id))return true;
        ##新增
        $res = $this->isUpdate(false)->save(compact('user_id','lesson_id'));
        Lesson::collect($lesson_id);
        return $res === false ? $this->setError('操作失败') : true;
    }

    /**
     * 取消收藏
     * @return bool
     * @throws Exception
     */
    protected function cancel(){
        $user_id = $this->user_id;
        $lesson_id = $this->lesson_id;
        ##查看是否已收藏
        if(!self::checkCollect($user_id, $lesson_id))return true;
        ##删除记录
        $res = $this->where(compact('user_id','lesson_id'))->delete();
        Lesson::cancelCollect($lesson_id);
        return $res === false ? $this->setError('操作失败') : true;
    }

    /**
     * 检查用户是否收藏
     * @param $user_id
     * @param $lesson_id
     * @return int|string
     * @throws \think\Exception
     */
    public static function checkCollect($user_id, $lesson_id){
        return self::where(compact('user_id','lesson_id'))->count();
    }

    /**
     * 用户收藏课程列表
     * @param $user
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collectList($user){
        ##验证
        if(!$this->valid->scene('lesson_collect_list')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $page = input('get.page',1,'intval');
        $size = input('get.size',6,'intval');
        ##数据
        $list = $this
            ->where([
                'user_id' => $user['user_id'],
            ])
            ->with(
                [
                    'lesson' => function(Query $query){
                        $query
                            ->with(
                                [
                                    'image' => function(Query $query){
                                        $query->field(['file_id', 'storage', 'file_url', 'file_name']);
                                    },
                                    'cate' => function(Query $query){
                                        $query->field(['lesson_cate_id', 'title']);
                                    },
                                    'lessonClass' => function(Query $query){
                                        $query->field(['class_id', 'lesson_id']);
                                    }
                                ]
                            )
                            ->field(['lesson_id', 'title', 'cover', 'desc', 'cate_id', 'lesson_type', 'lesson_size', 'create_time', 'watch_num', 'status']);
                    }
                ]
            )
            ->page($page, $size)
            ->order('create_time','desc')
            ->field(['lesson_id'])
            ->select();
        return compact('list');
    }

}