<?php


namespace app\common\model\college;


use app\common\model\BaseModel;

class CollegeWatchRecord extends BaseModel
{

    protected $name = 'college_watch_record';

    protected $insert = ['wxapp_id'];

    protected $errorCode = 0;

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

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
     * 课时信息
     * @return \think\model\relation\BelongsTo
     */
    public function collegeClass(){
        return $this->belongsTo('app\common\model\college\CollegeClass','class_id','class_id');
    }

}