<?php


namespace app\common\model;


class RebateLog extends BaseModel
{

    protected $name = 'rebate_log';

    protected $updateTime = false;

    /**
     * 新增返利日志
     * @param $data
     * @return false|int
     */
    public static function addLog($data){
        return (new self)->save($data);
    }

}