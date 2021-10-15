<?php


namespace app\store\model;

use app\common\model\GoodsExperience as GoodsExperienceModel;
use think\db\Query;
use think\Exception;

class GoodsExperience extends GoodsExperienceModel
{

    /**
     * 体验装排行榜
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function getRankList(){
        $is_true = input('post.rankType',true);
        $where = [
            'first_user_id'=>['GT', 0]
        ];
        if($is_true=='true')$where['is_fake'] = 0;
        ##获取体验装商品id
        $list = $this->alias('ge')
            ->join('order o', 'ge.order_id = o.order_id', 'LEFT')
            ->where(function($query){
                $query->where(
                    [
                        'ge.is_online' => 1,
                        'o.pay_status' => 20,
                        'o.order_status' => ['IN', [10, 30]]
                    ]
                )->whereOr(
                    [
                        'ge.is_online' => 0
                    ]
                );
            })
            ->where($where)
            ->group('ge.first_user_id')
            ->field(['ge.first_user_id', 'count(ge.user_id) as num', 'ge.first_user_id as member_num', 'ge.first_user_id as redirect_member_num'])
            ->order(['num'=>'desc', 'member_num'=>'desc', 'redirect_member_num'=>'desc'])
//            ->order(['num'=>'desc', 'redirect_member_num'=>'desc'])
            ->with(
                [
                    'first_user' => function (Query $query) {
                        $query->field(['user_id', 'nickName', 'avatarUrl']);
                    }
                ]
            )
            ->paginate(20,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getRankList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
//        $userModel = new User();
//        foreach ($list as &$item) {
//            $item['member_num'] = $userModel->getMemberNumAttr($item['first_user_id']);
//            $item['redirect_member_num'] = $userModel->getRedirectMemberNumAttr($item['first_user_id']);
//        }
        $list = sortArrByManyField($list, 'num', SORT_DESC, 'member_num', SORT_DESC, 'redirect_member_num', SORT_DESC, 'first_user_id', SORT_ASC);
        return compact('list','page','total');
    }

    /**
     * 体验装订单列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getOrderList(){
        $params = [
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
            'order_status' => input('order_status',10,'intval'),
            'keywords' => input('keywords','','search_filter')
        ];
        $this->setOrderListWhere($params);

        ##列表
        $list = $this->alias('ge')
            ->join('order o','ge.order_id = o.order_id','LEFT')
            ->join('user u','ge.user_id = u.user_id','LEFT')
            ->join('user fu','ge.first_user_id = fu.user_id','LEFT')
            ->join('user su','ge.second_user_id = su.user_id','LEFT')
            ->field(['ge.user_id', 'ge.first_user_id', 'ge.second_user_id', 'ge.order_id', 'ge.create_time'])
            ->with(
                [
                    'orderData.goods.image',
                    'firstUser',
                    'secondUser',
                    'user'
                ]
            )
            ->order('create_time','desc')
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getRankList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
        return compact('page','total','list');
    }

    /**
     * 体验装订单列表
     * @param $params
     */
    public function setOrderListWhere($params){
        $where['is_online'] = 1;
        ##订单状态
        switch($params['order_status']){
            case 1: ##待发货
                $where['o.pay_status'] = 20;
                $where['o.delivery_type'] = 10;
                $where['o.delivery_status'] = 10;
                $where['o.order_status'] = 10;
                break;
            case 2: ##待提货
                $where['o.pay_status'] = 20;
                $where['o.delivery_type'] = 20;
                $where['o.order_status'] = 10;
                break;
            case 3: ##已发货
                $where['o.pay_status'] = 20;
                $where['o.delivery_type'] = 10;
                $where['o.delivery_status'] = 20;
                $where['o.order_status'] = 10;
                break;
            case 4: ##已完成
                $where['o.pay_status'] = 20;
                $where['o.order_status'] = 30;
                break;
            default:
                break;
        }
        ##订单创建时间
        if($params['start_time'] && $params['end_time']){
            $start_time = strtotime($params['start_time']);
            $end_time = strtotime($params['end_time']);
            $where['ge.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        ##下单人、推荐人查询
        if($params['keywords']){
            $where['u.nickName|fu.nickName|su.nickName'] = ['LIKE', "%{$params['keywords']}%"];
        }
        if(isset($where))$this->where($where);
    }

}