<?php


namespace app\common\model\college;


use app\common\model\BaseModel;

class CollegeClassCode extends BaseModel
{

    protected $name = 'college_class_code';

    protected $pk = 'code_id';

    protected $updateTime = false;

    protected $insert = ['code'];

    /**
     * 设置私享码
     * @return string
     */
    public function setCodeAttr(){
        return $this->createCode();
    }

    /**
     * 生成私享码
     * @return string
     */
    protected function createCode(){
        return createCode(time());
    }

}