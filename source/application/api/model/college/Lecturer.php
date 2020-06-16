<?php


namespace app\api\model\college;

use app\common\model\college\Lecturer as LecturerModel;

class Lecturer extends LecturerModel
{

    /**
     * 增加关注数
     * @param $lecturer_id
     * @return int|true
     * @throws \think\Exception
     */
    public static function collect($lecturer_id){
        return self::where(['lecturer_id'=>$lecturer_id])->setInc('notice_num',1);
    }

    /**
     * 减少关注数
     * @param $lecturer_id
     * @return int|true
     * @throws \think\Exception
     */
    public static function cancel($lecturer_id){
        return self::where(['lecturer_id'=>$lecturer_id])->setDec('notice_num',1);
    }

}