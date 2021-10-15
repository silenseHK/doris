<?php


namespace app\agent\logic;

use app\agent\model\Order;
use app\agent\model\User;
use app\agent\model\user\GoodsStock;
use app\agent\model\user\GoodsStockLog;
use think\db\Query;

class UserLogic
{

    /**
     * 团队列表
     * @param $agent
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($agent){
        ##参数
        $func = input('post.func','ajax_go','str_filter');
        $size = input('post.size',20,'intval');
        $model = new User();
        $this->setListsWhere($model, $agent);
        $list = $model
            ->order('create_time desc, user_id desc')
            ->with(
                [
                    'grade' => function(Query $query){
                        $query->field(['grade_id', 'name']);
                    },
                ]
            )
            ->field(['user_id', 'grade_id', 'avatarUrl', 'city', 'country', 'province', 'create_time', 'integral', 'invitation_user_id', 'nickName', 'mobile', 'mobile as mobile_hide', 'invitation_user_id as invitation_user', 'user_id as stock'])
            ->paginate($size,false,['type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:'. $func .'([PAGE]);']);

        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('list','total','page');
    }

    /**
     * 设置
     * @param User $model
     * @param $agent
     */
    protected function setListsWhere($model, $agent){
        $where = [
            'relation'=>['LIKE', "%{$agent['user_id']}%"],
            'is_delete'=>0
        ];
        $keywords = input('post.keywords','','keywords_filter');
        $user_id = input('post.user_id',0,'intval');
        $grade_id = input('post.grade_id',0,'intval');
        if($user_id){
            $where['user_id'] = $user_id;
        }
        if($grade_id){
            $where['grade_id'] = $grade_id;
        }
        if($keywords){
            $where['nickName|mobile'] = ['LIKE', "%{$keywords}%"];
        }
        $model->where($where);
    }

    /**
     * 库存明细列表
     * @param $agent
     * @return array
     * @throws \think\exception\DbException
     */
    public function stockList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $size = input('post.size',20,'intval');
        $goods_sku_id = input('post.goods_sku_id',0,'intval');
        $model = new GoodsStockLog();
        ##数据
        $list = $model
            ->where(['user_id'=>$user_id, 'goods_sku_id'=>$goods_sku_id])
            ->with(
                [
                    'opposite_user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'grade_id'])->with(['grade'=>function(Query $query){
                            $query->field(['name', 'grade_id']);
                        }]);
                    },
                ]
            )
            ->order('create_time','desc')
            ->paginate($size,false,['type' => 'Bootstrap',
                'var_page' => 'page']);
        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('list','total');
    }

    /**
     * 库存信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function stock(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        ##数据
        $model = new GoodsStock();
        $list = $model
            ->where(compact('user_id'))
            ->field(['user_id', 'goods_id', 'goods_sku_id', 'stock', 'history_stock', 'history_sale', 'freeze_stock'])
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'spec' => function(Query $query){
                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with([
                            'image' => function(Query $query){
                                $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                            }
                        ]);
                    }
                ]
            )
            ->select();
        return compact('list');
    }

    public function memberList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id ? : $agent['user_id'];
        ##数据
        $model = new User();
        $list = $model->where(['relation'=>['LIKE', "%-{$user_id}-%"]])->field(['user_id', 'nickName', 'grade_id'])->with(['grade'=>function(Query $query){
            $query->field(['grade_id', 'name']);
        }])->select();
        if($list->isEmpty())return [];
        $list = $list->toArray();
        $list = memberTree($list,$user_id,'user_id');
        print_r($list);die;
    }

    /**
     * 直推团队成员列表
     * @param $agent
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function redirectMemberList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id ? : $agent['user_id'];
        ##数据
        $model = new User();
        $list = $model->where(['invitation_user_id'=>$user_id])->field(['user_id', 'nickName', 'grade_id', 'user_id as member_num'])->with(['grade'=>function(Query $query){
            $query->field(['grade_id', 'name']);
        }])->select();
        if($list->isEmpty())return [];
        $list = $list->toArray();
        foreach($list as &$item)$item['label'] = $item['nickName'];
        return $list;
    }

    /**
     * 统计数据
     * @param $agent
     * @return array
     */
    public function statistics($agent){
        return $this->filterStatistics($agent['user_id']);
    }

    /**
     * 计算会员人数
     * @param $model
     * @param $user_id
     * @param int $start_time
     * @param int $end_time
     * @param int $level
     * @return mixed
     */
    protected function countMemberNum($model, $user_id, $start_time=0, $end_time=0, $level=0){
        $where['relation'] = ['LIKE', "%-{$user_id}-%"];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        if($level){
            if(is_array($level))
                $where['grade_id'] = ['IN', $level];
            else
                $where['grade_id'] = $level;
        }
        return $model->where($where)->count();
    }

    /**
     * 生成统计数据
     * @param $user_id
     * @return array
     */
    protected function filterStatistics($user_id){
        $statistics = [
            'total' => [
                'start_time' => 0,
                'end_time' => 0,
                'title' => '历史统计'
            ],
            'day' => [
                'start_time' => get_day_start_timestamp(),
                'end_time' => get_day_end_timestamp(),
                'title' => '今日统计'
            ],
            'last_day' => [
                'start_time' => get_last_day_start_timestamp(),
                'end_time' => get_last_day_end_timestamp(),
                'title' => '昨日统计'
            ],
        ];
        $level = [
            'visitor' => [
                'title' => '游客',
                'value' => 1
            ],
            'week' => [
                'title' => '周体验',
                'value' => 2
            ],
            'month' => [
                'title' => '月体验',
                'value' => 3
            ],
            'vip' => [
                'title' => 'VIP特约',
                'value' => 4
            ],
            'agent' => [
                'title' => '总代',
                'value' => 5
            ],
            'strategy' => [
                'title' => '战略董事',
                'value' => 6
            ],
            'partner' => [
                'title' => '合伙人、董事',
                'value' => [7, 8]
            ],
            'total' => [
                'title' => '全部',
                'value' => 0
            ],
        ];
        $data = [];
        $model = new User();
        foreach($statistics as $key => $item){
            $data[$key] = [];
            $data[$key]['title'] = $item['title'];
            $data[$key]['statistics'] = [];
            foreach($level as $k => $it){
                $data[$key]['statistics'][$k] = [];
                $data[$key]['statistics'][$k]['statistics'] = $this->countMemberNum($model, $user_id, $item['start_time'], $item['end_time'], $it['value']);
                $data[$key]['statistics'][$k]['title'] = $it['title'];
            }
        }
        return $data;
    }

}