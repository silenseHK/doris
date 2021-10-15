<?php


namespace app\common\model;


use traits\model\SoftDelete;

class QuestionCate extends BaseModel
{

    protected $name = 'question_cate';

    protected $insert = ['wxapp_id'];

    protected $updateTime = false;

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

}