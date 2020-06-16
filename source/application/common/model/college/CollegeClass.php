<?php


namespace app\common\model\college;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class CollegeClass extends BaseModel
{

    protected $name = 'college_class';

    protected $pk = 'class_id';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    /**
     * 一对多 直播间
     * @return \think\model\relation\BelongsTo
     */
    public function liveRoom(){
        return $this->belongsTo('app\common\model\wxapp\LiveRoom','live_room_id','id');
    }

    /**
     * 课程
     * @return \think\model\relation\BelongsTo
     */
    public function lesson(){
        return $this->belongsTo('app\common\model\college\Lesson','lesson_id','lesson_id');
    }

    /**
     * 封面
     * @return \think\model\relation\BelongsTo
     */
    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','cover','file_id');
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
     * 计算课程已开展课时
     * @param $lesson_id
     * @return int|string
     * @throws \think\Exception
     */
    public static function countLessonClass($lesson_id){
        return self::where(['lesson_id'=>$lesson_id])->count();
    }

}