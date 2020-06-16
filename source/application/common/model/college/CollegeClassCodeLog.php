<?php


namespace app\common\model\college;


use app\common\model\BaseModel;

class CollegeClassCodeLog extends BaseModel
{

    protected $name = 'college_class_code_log';

    protected $pk = 'log_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

}