<?php


namespace app\common\model\college;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class Lesson extends BaseModel
{

    protected $name = 'college_lesson';

    protected $pk = 'lesson_id';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $errorCode = 0;

    protected $insert = ['wxapp_id'];

    /**
     * 课程类型
     * @var array
     */
    protected $lessonType = [
        '10' => [
            'value' => 10,
            'text' => '视频'
        ],
        '20' => [
            'value' => 20,
            'text' => '直播'
        ]
    ];

    /**
     * 设置错误信息
     * @param string $error
     * @param int $errorCode
     * @return bool
     */
    protected function setError($error='', $errorCode=0){
        if($error)
            $this->error = $error;
        if($errorCode)
            $this->errorCode = $errorCode;
        return false;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function getCode(){
        return $this->errorCode;
    }

    /**
     * 课程规模
     * @var array
     */
    protected $lessonSize = [
        '10' => [
            'value' => 10,
            'text' => '单课'
        ],
        '20' => [
            'value' => 20,
            'text' => '系列课'
        ]
    ];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

    /**
     * 讲师
     * @return \think\model\relation\BelongsTo
     */
    public function lecturer(){
        return $this->belongsTo('app\common\model\college\Lecturer','lecturer_id','lecturer_id');
    }

    /**
     * 设置分类面包屑
     * @param $value
     * @param $data
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCateCrumbsAttr($value, $data){
        $cate_info = LessonCate::getCateInfo($value);
        $crumbs = $cate_info['title'];
        if($cate_info['pid'] > 0){
            $cate_info2 = LessonCate::getCateInfo($cate_info['pid']);
            $crumbs = $cate_info2['title'] . '-' . $crumbs;
        }
        return $crumbs;
    }

    /**
     * 过滤简介
     * @param $value
     * @return string
     */
    public function getFilterDescAttr($value){
        return mb_substr($value, 0, 50, 'utf-8');
    }

    /**
     * 过滤详情
     * @param $value
     * @return string
     */
    public function getContentAttr($value){
        return htmlspecialchars_decode($value);
    }

    /**
     * 开放等级
     * @return \think\model\relation\BelongsToMany
     */
    public function limitGrade(){
        return $this->belongsToMany('app\common\model\user\Grade','app\common\model\college\LessonGrade','grade_id','lesson_id');
    }

    /**
     * 一对多 封面图
     * @return \think\model\relation\BelongsTo
     */
    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','cover','file_id');
    }

    /**
     * 分类
     * @return \think\model\relation\BelongsTo
     */
    public function cate(){
        return $this->belongsTo('app\common\model\college\LessonCate','cate_id','lesson_cate_id');
    }

    /**
     * 一节课
     * @return \think\model\relation\HasOne
     */
    public function lessonClass(){
        return $this->hasOne('app\common\model\college\CollegeClass','lesson_id','lesson_id');
    }

    /**
     * 处理课程类型
     * @param $value
     * @return mixed
     */
    public function getLessonTypeAttr($value){
        return $this->lessonType[$value];
    }

    /**
     * 处理课程规模
     * @param $value
     * @return mixed
     */
    public function getLessonSizeAttr($value){
        return $this->lessonSize[$value];
    }

    /**
     * 已开放课时
     * @param $value
     * @return int|string
     * @throws \think\Exception
     */
    public function getSizeNumAttr($value){
        return CollegeClass::countLessonClass($value);
    }

}