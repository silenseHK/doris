<?php

namespace app\store\model\user;

use app\common\model\user\BalanceLog as BalanceLogModel;
use app\store\model\Order;
use app\store\model\User;
use think\db\Query;

/**
 * 用户余额变动明细模型
 * Class BalanceLog
 * @package app\store\model\user
 */
class BalanceLog extends BalanceLogModel
{
    /**
     * 获取余额变动明细列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query = [])
    {
        // 设置查询条件
        !empty($query) && $this->setQueryWhere($query);
        // 获取列表数据
        return $this->with(['user'])
            ->alias('log')
            ->field('log.*')
            ->join('user', 'user.user_id = log.user_id')
            ->order(['log.create_time' => 'desc', 'log_id' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     * @param $query
     */
    private function setQueryWhere($query)
    {
        // 设置默认的检索数据
        $params = $this->setQueryDefaultValue($query, [
            'user_id' => 0,
            'search' => '',
            'scene' => -1,
            'start_time' => '',
            'end_time' => '',
        ]);
        // 用户ID
        $params['user_id'] > 0 && $this->where('log.user_id', '=', $params['user_id']);
        // 用户昵称
        !empty($params['search']) && $this->where('user.nickName', 'like', "%{$params['search']}%");
        // 余额变动场景
        $params['scene'] > -1 && $this->where('log.scene', '=', (int)$params['scene']);
        // 起始时间
        !empty($params['start_time']) && $this->where('log.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('log.create_time', '<', strtotime($params['end_time']) + 86400);
    }

    /**
     * 退款扣除
     * @param $user_id
     * @param $money
     * @param $order_id
     * @param $flag
     * @throws \think\Exception
     */
    public static function refund($user_id, $money, $order_id, $flag){
        $desc = $flag == 1? '货款' : '推荐奖励';
        $data = [
            'user_id' => $user_id,
            'scene' => 40,
            'money' => -$money,
            'describe' => "用户退款,返还{$desc}",
            'order_id' => $order_id
        ];
        User::where(['user_id'=>$user_id])->setDec('balance', $money);
        self::add(40, $data,'');
    }

    /**
     * 余额变动列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getUserBalanceList(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $where = [
            'user_id'=>$user_id
        ];
        if($start_time && $end_time){
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        ##数据
        $list = $this
            ->where($where)
            ->with(
                [
                    'orders' => function(Query $query){
                        $query->with(
                            [
                                'stockLog',
                                'balanceLog',
                                'supplyUser',
                                'user',
                                'supplyGrade',
                                'userGrade',
                                'goods' => function(Query $query){
                                    $query->with(['sku.image']);
                                }
                            ]
                        );
                    }
                ]
            )
            ->order('create_time','desc')
            ->paginate(10,false,[
                'type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:ajax_balance_go([PAGE]);'
            ]);

        $page = $list->render();
        return compact('page','list');
    }

}