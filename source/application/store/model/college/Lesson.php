<?php


namespace app\store\model\college;

use app\common\model\college\Lesson as LessonModel;
use app\store\model\user\Grade;
use app\store\model\wxapp\LiveRoom;
use app\store\validate\LessonValid;
use think\Db;
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

    public function index(){
        $params = [
            'lesson_type' => input('lesson_type',0,'intval'),
            'keywords' => input('keywords',0,'str_filter'),
        ];
        $this->setWhere($params);
        $list = $this
            ->with(
                [
                    'lecturer' => function(Query $query){
                        $query->field(['lecturer_id', 'name']);
                    },
                    'limit_grade',
                    'image'
                ]
            )
            ->field(['lesson_id', 'title', 'cover', 'desc', 'lecturer_id', 'is_public', 'is_private', 'is_grade', 'cate_id', 'lesson_type', 'lesson_size', 'total_size', 'create_time', 'watch_num', 'notice_num', 'status', 'sort', 'is_recom', 'cate_id as cate_crumbs', '`desc` as filter_desc'])
            ->order('create_time','desc')
            ->paginate(15,false, ['query'=>\request()->request()]);
        $list2 = $list->toArray();
        $data = $list2['data'];
        return array_merge(compact('list','data'), $params);
    }

    /**
     * 设置筛选条件
     * @param $params
     */
    public function setWhere($params){
        if($params['lesson_type']){
            $this->where(['lesson_type'=>$params['lesson_type']]);
        }
        if($params['keywords']){
            $lecturer_ids = Lecturer::where(['name'=>['LIKE', "%{$params['keywords']}%"]])->column('lecturer_id');
            $this->where(function($query) use ($lecturer_ids, $params){
                $query->where(['lecturer_id'=>['IN', $lecturer_ids]])->whereOr(['title'=>['LIKE', "%{$params['keywords']}%"]]);
            });
        }
    }

    /**
     * 新增信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addInfo(){
        return $this->commonInfo();
    }

    /**
     * 编辑信息
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editInfo(){
        $lesson_id = input('lesson_id',0,'intval');
        $lesson_info = self::get(['lesson_id'=>$lesson_id], ['image', 'limitGrade', 'lecturer', 'cate']);
        if(!$lesson_info)throw new Exception('课程信息不存在或已删除');
        ##一级分类id
        $lesson_info['first_cate'] = LessonCate::GetParentCateId($lesson_info['cate_id']);
        if($lesson_info['lesson_size']['value'] == 10){
            $lesson_info['class'] = CollegeClass::getSoloClassInfo($lesson_id);
        }
//        print_r($lesson_info->toArray());die;
        $common_info = $this->commonInfo();
        return array_merge(compact('lesson_info'), $common_info);
    }

    /**
     * 公共数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function commonInfo(){
        ##讲师
        $lecturer_list = Lecturer::getList();
        ##课程分类
        $lesson_cate_list = LessonCate::getCateList();
        ##直播间列表
        $live_room_list = LiveRoom::getRoomList();
        ##会员等级
        $grade_list = Grade::getGradeList();
        return compact('lecturer_list','lesson_cate_list', 'live_room_list', 'grade_list');
    }

    /**
     * 新增课程
     * @return bool
     * @throws Exception
     */
    public function add(){
        ##验证
        if(!$this->valid->scene('add')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $data = $this->filterParams();
        ##操作
        Db::startTrans();
        try{
            ##添加课程
            $res = $this->isUpdate(false)->allowField(true)->save($data);
            if($res === false)throw new Exception('课程添加失败');
            $lesson_id = $this->getLastInsID();
            ##添加
            if($data['is_grade']){
                $lesson_grade_data = $this->createLessonGradeData($data['grade'], $lesson_id);
                $res = (new LessonGrade)->saveAll($lesson_grade_data);
                if($res === false)throw new Exception('开放权限添加失败');
            }
            ##添加课时
            if($data['lesson_size'] == 10){
                $class_data = $this->createClassData($data, $lesson_id);
                $res = (new CollegeClass)->isUpdate(false)->save($class_data);
                if($res === false)throw new Exception('课时添加失败');
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 编辑课程
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function edit(){
        ##验证
        if(!$this->valid->scene('edit')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $lesson_id = input('post.lesson_id',0,'intval');
        ##课程原数据
        $lesson_info = self::get(['lesson_id'=>$lesson_id]);
        if(!$lesson_info)throw new Exception('未找到课程信息');
        $data = $this->filterParams();
        ##操作
        Db::startTrans();
        try{
            ##添加课程
            $res = $this->allowField(true)->save($data, ['lesson_id'=>$lesson_id]);
            if($res === false)throw new Exception('课程修改失败');
            ##删除以前的开放等级
            $lessonGradeModel = new LessonGrade();
            $lessonGradeModel->where(['lesson_id'=>$lesson_id])->delete();
            ##添加
            if($data['is_grade']){
                $lesson_grade_data = $this->createLessonGradeData($data['grade'], $lesson_id);
                $res = $lessonGradeModel->saveAll($lesson_grade_data);
                if($res === false)throw new Exception('开放权限修改失败');
            }
            ##添加课时
            if($data['lesson_size'] == 10){
                $class_data = $this->createClassData($data, $lesson_id);
                $res = (new CollegeClass)->save($class_data, ['lesson_id'=>$lesson_id]);
                if($res === false)throw new Exception('课时修改失败');
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 过滤参数
     * @return array
     * @throws Exception
     */
    protected function filterParams(){
        $data = [
            'title' => input('post.title','','str_filter'),
            'desc' => input('post.desc','','str_filter'),
            'cover' => input('post.cover',0,'intval'),
            'lecturer_id' => input('post.lecturer_id',0,'intval'),
            'is_public' => input('post.is_public',0,'intval'),
            'is_private' => input('post.is_private',0,'intval'),
            'is_grade' => input('post.is_grade',0,'intval'),
            'cate_id' => input('post.cate_id',0,'intval'),
            'lesson_type' => input('post.lesson_type',10,'intval'),
            'lesson_size' => input('post.lesson_size',10,'intval'),
            'total_size' => input('post.total_size',1,'intval'),
            'status' => input('post.status',1,'intval'),
            'sort' => input('post.sort',9999,'intval'),
            'is_recom' => input('post.is_recom',0,'intval'),
            'live_room_id' => input('post.live_room_id',0,'intval'),
            'video_url' => input('post.video_url','','str_filter'),
            'content' => input('post.content','','htmlspecialchars'),
            'grade' => input('post.grade/a', [])
        ];
        if($data['lesson_type']==10 && $data['lesson_size'] == 10 && !$data['video_url'])throw new Exception('请上传视频');
        if($data['lesson_type']==20 && $data['lesson_size'] == 10 && !$data['live_room_id'])throw new Exception('请选择直播间');
        if(!$data['is_public'] && !$data['is_private'] && !$data['is_grade'])throw new Exception('请选择一种私享类型');
        if($data['is_grade'] && empty($data['grade']))throw new Exception('请至少选择一种开放会员等级');
        return $data;
    }

    /**
     * 构造课程等级权限数据
     * @param $grade
     * @param $lesson_id
     * @return array
     */
    protected function createLessonGradeData($grade, $lesson_id){
        $data = [];
        foreach($grade as $item){
            $data[] = [
                'grade_id' => $item,
                'lesson_id' => $lesson_id
            ];
        }
        return $data;
    }

    /**
     * 构建课时数据
     * @param $params
     * @param $lesson_id
     * @return array
     */
    protected function createClassData($params, $lesson_id){
        $data = [
            'lesson_id' => $lesson_id,
            'cover' => $params['cover'],
            'title' => $params['title'],
            'desc' => $params['desc'],
            'content' => $params['content'],
            'video_url' => $params['video_url'],
            'live_room_id' => $params['live_room_id'],
            'is_free' => 0,
            'sort' => $params['sort'],
            'status' => $params['status'],
        ];
        return $data;
    }

    /**
     * 删除
     * @return bool
     * @throws Exception
     */
    public function del(){
        $lesson_id = input('post.lesson_id',0,'intval');
        if(!$lesson_id)throw new Exception('参数错误');
        try{
            ##删除课程
            $res = self::destroy($lesson_id);
            if($res === false)throw new Exception('操作失败');
            ##删除课时
            $res = CollegeClass::where(['lesson_id'=>$lesson_id])->setField('delete_time', time());
            if($res === false)throw new Exception('操作失败.');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 修改字段数据
     * @return int|mixed
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function changeField(){
        ##验证
        if(!$this->valid->scene('change_field')->check(input()))throw new Exception($this->valid->getError());
        $lesson_id = input('post.lesson_id',0,'intval');
        $field = input('post.field','','str_filter');
        $value = input('post.value','','str_filter');
        $data = self::get($lesson_id);
        if(!$data)throw new Exception('数据不存在或已删除');
        if(!isset($data[$field]))throw new Exception('字段不存在');
        if(!$value){
            $value = ($data[$field] + 1) % 2;
        }
        $res = $this->where(['lesson_id'=>$lesson_id])->setField($field, $value);
        if($res === false)throw new Exception('操作失败');
        return $value;
    }

}