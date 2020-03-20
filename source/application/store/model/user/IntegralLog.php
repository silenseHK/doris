<?php


namespace app\store\model\user;

use app\common\model\user\IntegralLog as IntegralLogModel;

class IntegralLog extends IntegralLogModel
{

    protected $updateTime = false;

    /**
     * 写入日志
     * @param $options
     * @return false|int
     */
    public static function addLog($options)
    {
        return (new self)->save($options);
    }


}