<?php


namespace app\api\model\college;

use app\common\enum\college\OpenType;
use app\common\model\college\Lesson as LessonModel;
use app\api\validate\college\LessonValid;
use think\db\Query;
use think\Exception;

class Lesson extends LessonModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new LessonValid();
    }

    /**
     * 公开状态
     * @param $value
     * @return mixed
     */
    public function getIsPublicAttr($value){
        return OpenType::publicData()[$value];
    }

    /**
     * 私享状态
     * @param $value
     * @return mixed
     */
    public function getIsPrivateAttr($value){
        return OpenType::PrivateData()[$value];
    }

    /**
     * 等级查看状态
     * @param $value
     * @return mixed
     */
    public function getIsGradeAttr($value){
        return OpenType::gradeData()[$value];
    }

    /**
     * 获取分类列表
     * @return array
     */
    public function getCateList(){
        $cate_list = LessonCate::getLists();
        return compact('cate_list');
    }

    /**
     * 课程列表
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getLessonList(){
        ##验证
        if(!$this->valid->scene('lesson_list')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $size = input('get.size',6,'intval');
        ##数据
        $this->setWhere();
        $list = $this
            ->with(
                [
                    'cate' => function(Query $query){
                        $query->field(['lesson_cate_id', 'title']);
                    },
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    },
                    'lessonClass' => function(Query $query){
                        $query->field(['class_id', 'lesson_id']);
                    }
                ]
            )
            ->order('sort','asc')
            ->field(['lesson_id', 'title', 'cover', 'cate_id', 'lesson_type', 'lesson_size', 'create_time', 'watch_num'])
            ->paginate($size,false,['query'=>\request()->request()]);
        return compact('list');
    }

    /**
     * 设置课程列表筛选条件
     */
    protected function setWhere(){
        $cate_pid = input('get.cate_pid',0,'intval');
        $cate_id = input('get.cate_id',0,'intval');
        $where = [
            'status' => 1
        ];
        if(!$cate_pid){ ##推荐课程
            $where['is_recom'] = 1;
        }else{
            if($cate_id)
                $where['cate_id'] = $cate_id;
            else{
                $cate_ids = LessonCate::where(['pid'=>$cate_pid])->column('lesson_cate_id');
                $where['cate_id'] = ['IN', $cate_ids];
            }
        }
        $this->where($where);
    }

    /**
     * 课程详情[系列课]
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getLessonDetail($user){
        ##验证
        if(!$this->valid->scene('lesson_detail')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $lesson_id = input('get.lesson_id',0,'intval');
        $info = self::get(
            ['lesson_id'=>$lesson_id],
            [
                'image' => function(Query $query){
                    $query->field(['file_id', 'storage', 'file_name']);
                },
                'lecturer' => function(Query $query){
                    $query
                        ->field(['lecturer_id', 'name', 'avatar', 'label', 'desc'])
                        ->with(
                            [
                                'image'=>function(Query $query){
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
        if(!$info)throw new Exception('课程数据不存在');
        if($info['status'] != 1)throw new Exception('课程已下架');
        ##判断是否关注
        $is_collect = 0;
        if($user){
            $is_collect = LecturerCollect::checkCollect($user['user_id'], $info['lecturer_id']);
        }
        $info['is_collect'] = $is_collect;
        $info['class_list'] = (new CollegeClass)->getClassList($lesson_id);
        $info['class_num'] = CollegeClass::countLessonClassNum($lesson_id);
        return compact('info');
    }

    /**
     * 验证查看权限
     * @param $user
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function checkAccess($user){
        ##验证
        if(!$this->valid->scene('check_access')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $class_id = input('post.class_id',0,'intval');
        $class_info = CollegeClass::get(
            [
                'class_id' => $class_id
            ],
            [
                'lesson' => function(Query $query){
                    $query->with(['limitGrade']);
                }
            ]
        );
        if(!$class_info)throw new Exception('课程数据不存在');
        if($class_info['status'] != 1)throw new Exception('课程已下架');

        if($class_info['is_free']){ ##免费试看
            return 'free';
        }
        if($class_info['lesson']['is_public'] == 1){ ##开放
            return 'public';
        }
        if($class_info['lesson']['is_grade'] == 1){ ##等级开放
            foreach($class_info['lesson']['limit_grade'] as $item){
                if($item['grade_id'] == $user['grade_id']){
                    return 'grade';
                }
            }
        }
        if($class_info['lesson']['is_private'] == 1){ ##私享码
            $class_code = input('post.class_code','','str_filter');
            if($class_code){
                $codeModel = new CollegeClassCode();
                if($codeModel->check($class_id, $class_code, $user['user_id']))return 'code';
                throw new Exception($codeModel->getError());
            }else{
                return false;
            }
        }
        throw new Exception('无查看权限');
    }

    /**
     * 讲师课程列表
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function lecturerLessonList(){
        ##验证
        if(!$this->valid->scene('lecturer_lesson_list')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $lecturer_id = input('get.lecturer_id',0,'intval');
        $lesson_size = input('get.lesson_size',10,'intval');
        $size = input('get.size',6,'intval');
        ##数据
        $list = $this
            ->where([
                'lecturer_id' => $lecturer_id,
                'status' => 1,
                'lesson_size' => $lesson_size
            ])
            ->with(
                [
                    'cate' => function(Query $query){
                        $query->field(['lesson_cate_id', 'title']);
                    },
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    },
                    'lessonClass' => function(Query $query){
                        $query->field(['class_id', 'lesson_id']);
                    }
                ]
            )
            ->order('sort','asc')
            ->field(['lesson_id', 'title', 'cover', 'cate_id', 'lesson_type', 'lesson_size', 'create_time', 'watch_num'])
            ->paginate($size,false,['query'=>\request()->request()]);
        return compact('list');
    }

    /**
     * 更新观看记录
     * @param $user
     * @throws Exception
     */
    public function updateWatchRecord($user){
        $class_id = input('post.class_id',0,'intval');
        $user_id = $user['user_id'];
        ##检查是否观看过
        $check = CollegeWatchRecord::checkWatched(compact('user_id','class_id'));
        if(!$check){##未观看过 增加观看记录
            $lesson_id = CollegeClass::where(['class_id'=>$class_id])->value('lesson_id');
            CollegeWatchRecord::add(compact('class_id','user_id', 'lesson_id'));
            ##增肌观看次数
            CollegeClass::watch($class_id, $lesson_id);
        }else{
            CollegeWatchRecord::edit(compact('class_id','user_id'));
        }
    }

    /**
     * 搜索
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function search(){
        ##验证
        if(!$this->valid->scene('search')->check(input()))
            return $this->setError($this->valid->getError());
        ##参数
        $keywords = input('get.keywords','','search_filter');
        $size = input('get.size',6,'intval');
        $page = input('get.page',1,'intval');
        if(!$keywords)
            return $this->setError('请输入正确的关键词');
        ##数据
        $list = $this->alias('l')
            ->join('college_lecturer cl','cl.lecturer_id = l.lecturer_id','LEFT')
            ->where([
                'l.status' => 1
            ])
            ->where(function($query) use ($keywords){
                $query->where([
                    'l.title' => ['LIKE', "%{$keywords}%"]
                ])
                ->whereOr(
                    ['cl.name'=> ['LIKE', "%{$keywords}%"]]
                );
            })
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
            ->order('watch_num','desc')
            ->page($page, $size)
            ->field(['l.lesson_id', 'l.title', 'l.cover', 'l.cate_id', 'l.lesson_type', 'l.lesson_size', 'l.create_time', 'l.watch_num'])
            ->select();
        return compact('list');
    }

    /**
     * 增加课程观看数
     * @param $lesson_id
     * @return int|true
     * @throws Exception
     */
    public static function watchLesson($lesson_id){
        return self::where(['lesson_id'=>$lesson_id])->setInc('watch_num',1);
    }

    /**
     * 收藏课程=》增加课程收藏数
     * @param $lesson_id
     * @return int|true
     * @throws Exception
     */
    public static function collect($lesson_id){
        return self::where(['lesson_id'=>$lesson_id])->setInc('notice_num',1);
    }

    /**
     * 取消收藏课程=》减少课程收藏数
     * @param $lesson_id
     * @return int|true
     * @throws Exception
     */
    public static function cancelCollect($lesson_id){
        return self::where(['lesson_id'=>$lesson_id])->setDec('notice_num',1);
    }

}