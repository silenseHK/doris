<?php


namespace app\store\model\user;

use app\common\model\user\IntegralLog as IntegralLogModel;
use app\store\model\User;
use think\Hook;

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

    /**
     * 退款返回积分
     * @param $info
     * @param $order_id
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function refund($info, $order_id){
        $user = User::get(['user_id'=>$info['user_id']]);
        ##减少用户积分
        User::where(['user_id'=>$info['user_id']])->setDec('integral', $info['change_integral']);
        ##增加变动日志
        $data = [
            'user_id' => $info['user_id'],
            'balance_integral' => $user['integral'],
            'change_integral' => $info['change_integral'],
            'change_direction' => 20,
            'change_type' => 10,
            'wxapp_id' => self::$wxapp_id,
            'order_id' => $order_id
        ];
        self::addLog($data);
        $integralLogId = (new self)->getLastInsID();
        $options = [
            'user_id' => $info['user_id'],
            'integral_log_id' => $integralLogId
        ];
        ### 刷新用户会员等级
        Hook::listen('user_instant_grade',$options);
    }

}