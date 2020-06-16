<?php


namespace app\api\model\college;

use app\common\model\college\CollegeClassCodeLog as CollegeClassCodeLogModel;

class CollegeClassCodeLog extends CollegeClassCodeLogModel
{

    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public static function add($data){
        return (new self)->isUpdate(false)->save($data);
    }

}