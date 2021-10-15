<?php


namespace app\common\model;


use traits\model\SoftDelete;

class Qrcode extends BaseModel
{

    protected $name = 'qrcode';

    protected $updateTime = false;

    protected $errorCode = 0;

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

    /**
     * 关联二维码
     * @return \think\model\relation\HasOne
     */
    public function image(){
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
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

}