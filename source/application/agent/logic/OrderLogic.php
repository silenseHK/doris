<?php


namespace app\agent\logic;

use app\agent\model\Order;
use app\agent\model\OrderDelivery;
use think\db\Query;

class OrderLogic extends BaseLogic
{

    /**
     * 消费、补货订单列表
     * @param $agent
     * @return array
     * @throws \think\exception\DbException
     */
    public function orderList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id?:$agent['user_id'];
        ##数据
        $model = new Order();
        $this->setOrderListWhere($model, $user_id);
        $list = $model
            ->field(['order_id', 'order_no', 'supply_user_id', 'total_price', 'express_price', 'pay_status', 'pay_type', 'delivery_type', 'express_id', 'express_no', 'order_status', 'supply_user_grade_id', 'rebate_info', 'rebate_money', 'delivery_status', 'receipt_status', 'create_time'])
            ->order('create_time','desc')
            ->with(
                [
                    'supplyUser',
                    'supplyGrade',
                    'goods' => function(Query $query){
                        $query->field(['order_id', 'order_goods_id', 'goods_name', 'goods_id', 'goods_sku_id', 'goods_attr', 'total_num', 'goods_price', 'image_id'])
                        ->with(['image'=>function(Query $query){
                            $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                        }]);
                    },
                    'express' => function(Query $query){
                        $query->field(['express_id', 'express_name']);
                    },
                    'address',
                    'extract',
                ]
            )
            ->paginate(20,false,['type' => 'Bootstrap',
                'var_page' => 'page']);
        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('list','total');
    }

    protected function setOrderListWhere($model, $user_id){
        $where = [
            'user_id' => $user_id,
            'is_delete' => 0
        ];
        ##订单号
        $order_no= input('post.order_no','','str_filter');
        if($order_no){
            $where['order_no'] = ['LIKE', "%{$order_no}%"];
        }
        ##订单状态
        $order_status = input('post.order_status',0,'intval');
        if($order_status > 0){
            switch($order_status){
                case 10: ##待付款
                    $where['order_status'] = 10;
                    $where['pay_status'] = 10;
                    break;
                case 20: ##待发货
                    $where['order_status'] = 10;
                    $where['delivery_status'] = 10;
                    $where['pay_status'] = 20;
                    break;
                case 30: ##待收货
                    $where['order_status'] = 10;
                    $where['delivery_status'] = 20;
                    $where['pay_status'] = 20;
                    break;
                case 40: ##已完成
                    $where['order_status'] = 30;
                    break;
                case 50: ##已退款
                    $where['order_status'] = 40;
                    break;
                case 60: ##已取消
                    $where['order_status'] = 20;
                    break;
                default:
                    break;
            }
        }
        ##发货类型
        $delivery_type = input('post.delivery_type',0,'intval');
        if($delivery_type > 0){
            $where['delivery_type'] = $delivery_type;
        }
        ##创建时间
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        $model->where($where);
    }

    public function deliveryList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id?:$agent['user_id'];
        ##数据
        $model = new OrderDelivery();
        $this->setDeliveryListWhere($model, $user_id);
        $list = $model
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'spec' => function(Query $query){
                        $query
                            ->field(['goods_sku_id', 'image_id', 'spec_sku_id'])
                            ->with(
                                [
                                    'image'=>function(Query $query){
                                        $query->field(['file_id', 'file_name', 'file_url', 'storage']);
                                    }
                                ]
                            );
                    }
                ]
            )
            ->field(['deliver_id', 'order_no', 'goods_id', 'goods_sku_id', 'goods_num', 'address', 'receiver_user', 'receiver_mobile', 'express_id', 'express_no', 'freight_money', 'create_time', 'deliver_type', 'deliver_status', 'extract_shop_id', 'deliver_time'])
            ->paginate(8,false,['type' => 'Bootstrap',
                'var_page' => 'page']);
        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('total','list');
    }

    protected function setDeliveryListWhere($model, $user_id){
        $where = [
            'user_id' => $user_id,
            'pay_status' => 20
        ];
        ##订单号
        $order_no= input('post.order_no','','str_filter');
        if($order_no){
            $where['order_no'] = ['LIKE', "%{$order_no}%"];
        }
        ##订单状态
        $order_status = input('post.order_status',0,'intval');
        if($order_status > 0){
            switch($order_status){
                case 10: ##待发货
                    $where['deliver_status'] = 10;
                    break;
                case 20: ##待收货
                    $where['deliver_status'] = 20;
                    break;
                case 30: ##待收货
                    $where['deliver_status'] = 40;
                    break;
                default:
                    break;
            }
        }
        ##发货类型
        $delivery_type = input('post.delivery_type',0,'intval');
        if($delivery_type > 0){
            $where['deliver_type'] = $delivery_type;
        }
        ##创建时间
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        $model->where($where);
    }

    /**
     * 团队消费订单
     * @param $agent
     * @return array
     * @throws \think\exception\DbException
     */
    public function teamOrderList($agent){
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id ? : $agent['user_id'];
        ##数据
        $model = new Order();
        $this->setTeamOrderListWhere($model, $user_id);
        $list = $model->alias('o')
            ->join('user u','o.user_id = u.user_id','LEFT')
            ->field(['o.order_id', 'o.order_no', 'o.supply_user_id', 'o.total_price', 'o.express_price', 'o.pay_status', 'o.pay_type', 'o.delivery_type', 'o.express_id', 'o.express_no', 'o.order_status', 'o.supply_user_grade_id', 'o.rebate_info', 'o.rebate_money', 'o.delivery_status', 'o.receipt_status', 'o.create_time', 'o.user_grade_id', 'o.user_id'])
            ->order('o.create_time','desc')
            ->with(
                [
                    'supplyUser' => function(Query $query){
                        $query->field(['user_id', 'nickName']);
                    },
                    'supplyGrade' => function(Query $query){
                        $query->field(['grade_id', 'name']);
                    },
                    'goods' => function(Query $query){
                        $query->field(['order_id', 'order_goods_id', 'goods_name', 'goods_id', 'goods_sku_id', 'goods_attr', 'total_num', 'goods_price', 'image_id'])
                            ->with(['image'=>function(Query $query){
                                $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                            }]);
                    },
                    'express' => function(Query $query){
                        $query->field(['express_id', 'express_name']);
                    },
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName']);
                    },
                    'userGrade' => function(Query $query){
                        $query->field(['grade_id', 'name']);
                    },
                    'address',
                    'extract',
                ]
            )
            ->paginate(10,false,['type' => 'Bootstrap',
                'var_page' => 'page']);
        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('list','total');
    }

    /**
     * 设置团队消费补货订单筛选条件
     * @param $model
     * @param $user_id
     */
    protected function setTeamOrderListWhere($model, $user_id){
        $where = [
            'u.relation' => ['LIKE', "%-{$user_id}-%"],
            'o.is_delete' => 0,
            'u.is_delete' => 0,
        ];

        $buy_user_id = input('post.buy_user_id',0,'intval');
        if($buy_user_id)
            $where['o.user_id'] = $buy_user_id;

        ##订单状态
        $order_status = input('post.order_status',0,'intval');
        if($order_status > 0){
            switch($order_status){
                case 10: ##待付款
                    $where['o.order_status'] = 10;
                    $where['o.pay_status'] = 10;
                    break;
                case 20: ##待发货
                    $where['o.order_status'] = 10;
                    $where['o.delivery_status'] = 10;
                    $where['o.pay_status'] = 20;
                    break;
                case 30: ##待收货
                    $where['o.order_status'] = 10;
                    $where['o.delivery_status'] = 20;
                    $where['o.pay_status'] = 20;
                    break;
                case 40: ##已完成
                    $where['o.order_status'] = 30;
                    break;
                case 50: ##已退款
                    $where['o.order_status'] = 40;
                    break;
                case 60: ##已取消
                    $where['o.order_status'] = 20;
                    break;
                default:
                    break;
            }
        }

        ##订单号
        $order_no= input('post.order_no','','str_filter');
        if($order_no)
            $where['o.order_no'] = ['LIKE', "%{$order_no}%"];

        ##发货类型
        $delivery_type = input('post.delivery_type',0,'intval');
        if($delivery_type > 0)
            $where['o.delivery_type'] = $delivery_type;

        ##创建时间
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time)
            $where['o.create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];

        $model->where($where);
    }

    /**
     * 团队提货发货订单
     * @param $agent
     * @return array
     * @throws \think\exception\DbException
     */
    public function teamDeliveryList($agent){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $user_id = $user_id?:$agent['user_id'];
        ##数据
        $model = new OrderDelivery();
        $this->setTeamDeliveryListWhere($model, $user_id);
        $list = $model->alias('od')
            ->join('user u','u.user_id = od.user_id','LEFT')
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'spec' => function(Query $query){
                        $query
                            ->field(['goods_sku_id', 'image_id', 'spec_sku_id'])
                            ->with(
                                [
                                    'image'=>function(Query $query){
                                        $query->field(['file_id', 'file_name', 'file_url', 'storage']);
                                    }
                                ]
                            );
                    },
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'grade_id'])->with(['grade'=>function(Query $query){$query->field(['grade_id', 'name']);}]);
                    }
                ]
            )
            ->field(['od.deliver_id', 'od.order_no', 'od.goods_id', 'od.goods_sku_id', 'od.goods_num', 'od.address', 'od.receiver_user', 'od.receiver_mobile', 'od.express_id', 'od.express_no', 'od.freight_money', 'od.create_time', 'od.deliver_type', 'od.deliver_status', 'od.extract_shop_id', 'od.deliver_time'])
            ->paginate(10,false,['type' => 'Bootstrap',
                'var_page' => 'page']);
        $total = $list->total();
        if(!$list->isEmpty())
            $list = $list->toArray()['data'];
        else
            $list = [];
        return compact('total','list');
    }

    protected function setTeamDeliveryListWhere($model, $user_id){
        $where = [
            'od.user_id' => $user_id,
            'od.pay_status' => 20,
            'u.relation' => ['LIKE', "%-{$user_id}-%"],
            'u.is_delete' => 0
        ];
        ##订单号
        $order_no= input('post.order_no','','str_filter');
        if($order_no){
            $where['od.order_no'] = ['LIKE', "%{$order_no}%"];
        }
        ##订单状态
        $order_status = input('post.order_status',0,'intval');
        if($order_status > 0){
            switch($order_status){
                case 10: ##待发货
                    $where['od.deliver_status'] = 10;
                    break;
                case 20: ##待收货
                    $where['od.deliver_status'] = 20;
                    break;
                case 30: ##待收货
                    $where['od.deliver_status'] = 40;
                    break;
                default:
                    break;
            }
        }
        ##发货类型
        $delivery_type = input('post.delivery_type',0,'intval');
        if($delivery_type > 0){
            $where['od.deliver_type'] = $delivery_type;
        }
        ##创建时间
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time){
            $where['od.create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        $model->where($where);
    }

}