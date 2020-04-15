<?php

namespace app\api\model\user;

use app\common\model\user\BalanceLog as BalanceLogModel;

/**
 * 用户余额变动明细模型
 * Class BalanceLog
 * @package app\api\model\user
 */
class BalanceLog extends BalanceLogModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
    ];

    /**
     * 获取账单明细列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId)
    {
        // 获取列表数据
        return $this->where('user_id', '=', $userId)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取收入金额
     * @param $start_time
     * @param $end_time
     * @param $order_id
     * @param $user_id
     * @param $scene
     * @return float|int
     */
    public static function getIncome($start_time, $end_time, $order_id, $user_id, $scene){
        $where = compact('user_id','scene');
        if($start_time && $end_time)$where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        if($order_id)$where['order_id'] = $order_id;
        return self::where($where)->sum('money');
    }

}