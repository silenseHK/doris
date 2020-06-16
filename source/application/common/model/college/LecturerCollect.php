<?php


namespace app\common\model\college;


use app\common\model\BaseModel;

class LecturerCollect extends BaseModel
{

    protected $name = 'college_lecturer_collect';

    protected $updateTime = false;

    protected $errorCode = 0;

    protected $insert = ['wxapp_id'];

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
     * 讲师
     * @return \think\model\relation\BelongsTo
     */
    public function lecturer(){
        return $this->belongsTo('app\common\model\college\Lecturer','lecturer_id','lecturer_id');
    }

}