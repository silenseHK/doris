<?php


namespace app\common\model\admin;


use app\common\model\BaseModel;

class HandleLog extends BaseModel
{

    protected $name = 'admin_handle_log';

    protected $pk = 'log_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id', 'request_type'];

    protected $requestType = [
        'get' => 1,
        'post' => 2
    ];

    public function setWxappIdAttr()
    {
        return self::$wxapp_id ?: 10001;
    }

    public function setRequestTypeAttr(){
        return $this->requestType[strtolower($_SERVER['REQUEST_METHOD'])];
    }

    public function getRequestTypeAttr($value){
        return array_flip($this->requestType)[$value];
    }

    public function admin(){
        return $this->belongsTo('app\common\model\admin\User','admin_id','admin_user_id');
    }

    public function access(){
        return $this->belongsTo('app\common\model\store\Access','access_id','access_id');
    }

}