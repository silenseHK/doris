<?php


namespace app\store\model;


use app\common\library\wechat\WxSubMsg;
use app\common\model\user\OrderDeliver;
use app\store\service\order\Export as Exportservice;
use app\store\validate\OrderDeliverValid;
use think\Db;
use think\db\Query;
use think\Exception;
use app\common\service\order\Refund as RefundService;
use app\common\model\UserGoodsStock;
use app\store\model\UserGoodsStock as StoreUserGoodsStock;
use app\store\model\Wxapp as WxappModel;

class OrderDelivery extends OrderDeliver
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new OrderDeliverValid();
    }

    /**
     * 构造数据
     * @param $params
     * @return array
     * @throws \think\exception\DbException
     */
    public function makeData($params){
        $list = $this->getList($params);
        $deliveryTypeList = $this->getDeliveryTypeList();
        $title = "自提发货订单";
        return compact('list','deliveryTypeList','title');
    }

    /**
     * 获取列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($params){
        $this->setWhere($params);
        $list = $this->alias('od')
            ->join('user u','u.user_id = od.user_id','LEFT')
            ->join('user_grade ug','ug.grade_id = u.grade_id','LEFT')
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual'])->with(['image.file']);
                    },
                    'spec.image'
                ]
            )
            ->field(['od.deliver_id', 'od.order_no', 'od.user_id', 'od.goods_id', 'od.goods_sku_id', 'od.goods_num', 'od.address', 'od.receiver_user', 'od.receiver_mobile', 'od.express_id', 'od.express_no', 'od.freight_money', 'od.remark', 'od.create_time', 'od.deliver_type', 'od.deliver_status', 'od.pay_status', 'od.pay_time', 'u.nickName', 'u.avatarUrl', 'u.mobile', 'u.grade_Id', 'ug.name as grade_name'])
            ->order('create_time','desc')
            ->paginate(10,false,['query' => \request()->request()]);
        return $list;
    }

    /**
     * 设置查询条件
     * @param $params
     */
    public function setWhere($params){
        if(isset($params['order_no'])){
            $order_no = str_filter($params['order_no']);
            if($order_no)
                $where['od.order_no'] = $order_no;
        }
        if(isset($params['keywords'])){
            $keywords = str_filter($params['keywords']);
            if($keywords)
                $where['u.nickName|u.mobile'] = ["LIKE", "%{$params['keywords']}%"];
        }
        if(isset($params['start_time']) && $params['start_time'] && isset($params['end_time']) && $params['end_time']){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $end_time = strtotime($params['end_time'] . " 23:59:59");
            $where['od.create_time'] = ['between', [$start_time, $end_time]];
        }
        if(isset($params['start_time']) && $params['start_time'] && !isset($end_time)){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $where['od.create_time'] = ['EGT', $start_time];
        }
        if(isset($params['end_time']) && $params['end_time'] && !isset($start_time)){
            $end_time = strtotime($params['end_time'] . " 23:59:59");
            $where['od.create_time'] = ['ELT', $end_time];
        }
        if(isset($params['deliver_type']) && $params['deliver_type'] > 0){
            $where['od.deliver_type'] = intval($params['deliver_type']);
        }
        if(isset($params['deliver_status']) && $params['deliver_status'] > 0){
            $where['deliver_status'] = intval($params['deliver_status']);
        }
        $where['od.pay_status'] = 20;
        if(isset($where) && !empty($where))
            $this->where($where);
    }

    /**
     * 获取发货类型
     * @return array
     */
    public function getDeliveryTypeList(){
        return $this->deliver_type;
    }

    /**
     * 确认已自提操作
     * @param $deliver_id
     * @param int $complete_type
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function submitSelfOrder($deliver_id, $complete_type=20){
        $model = self::get(compact('deliver_id'));
        if($model['deliver_type']['value'] != 20){
            throw new Exception('非自提订单');
        }
        if($model['deliver_status']['value'] != 20){
            throw new Exception('订单不支持确认自提');
        }
        if($model['pay_status']['value'] != 20){
            throw new Exception('订单不支持此操作');
        }
        ##执行操作
        Db:: startTrans();
        try{
            $res = $model->isUpdate()->save([
                'deliver_status' => 40,
                'complete_time' => time(),
                'complete_type' => $complete_type
            ],['deliver_id'=>$model['deliver_id']]);
            if($res === false)throw new Exception('操作失败');
            ##减少冻结库存
            if(UserGoodsStock::disFreezeStockByUserGoodsId($model['user_id'], $model['goods_sku_id'], $model['goods_num'],1) === false)throw new Exception('操作失败');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

    }

    /**
     * 执行取消发货操作
     * @param $deliver_id
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function cancelOrder($deliver_id){
        $model = self::get(compact('deliver_id'));
        if($model['deliver_status']['value'] != 10 && $model['deliver_type'] == 10){
            throw new Exception('订单不支持此操作');
        }
        if($model['pay_status']['value'] != 20){
            throw new Exception('订单不支持此操作');
        }
        Db::startTrans();
        try{
            ##修改状态
            $res = $model->isUpdate(true)->save([
                'deliver_status' => 30
            ]);
            if($res === false)throw new Exception('操作失败');
            ##返还库存
            $res = StoreUserGoodsStock::backStock($model);
            if($res !== true)throw new Exception('库存返还失败');
            ##退款
            if($model['deliver_type']['value'] == 10 && $model['freight_money'] > 0){
                $reback = (new RefundService)->freight($model);
                if($reback !== true)throw new Exception('运费退款失败');
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 订单详情
     * @param $deliver_id
     * @return OrderDelivery|null
     * @throws \think\exception\DbException
     */
    public static function detail($deliver_id){
        return self::get(['deliver_id'=>$deliver_id],
            [
                'user',
                'goods' => function(Query $query){
                    $query->with(['image.file', 'specs']);
                },
                'express',
                'spec.image'
            ]
        );
    }

    /**
     * 确认发货
     * @param $post
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function deliver($post){
        ##验证
        $res = $this->valid->scene('deliver')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        $deliver_id = intval($post['deliver_id']);
        $order = self::get(compact('deliver_id'), ['user', 'goods']);
        if($order['pay_status']['value'] != 20)throw new Exception('订单不支持此操作');
        if($order['deliver_type']['value'] != 10)throw new Exception('订单不支持此操作');
        if($order['deliver_status']['value'] != 10)throw new Exception('订单不支持此操作');

        ##更新订单信息
        $data = [
            'express_id' => intval($post['order']['express_id']),
            'express_no' => str_filter($post['order']['express_no']),
            'express_remark' => str_filter($post['order']['express_remark']),
            'deliver_status' => 20,
            'deliver_time' => time()
        ];
        $res = $this->isUpdate(true)->save($data, compact('deliver_id'));
        if($res === false)throw new Exception('操作失败');

        ##发送订阅消息
        $config = WxappModel::getWxappCache();
        $wxSubMsg = new WxSubMsg($config['app_id'], $config['app_secret']);
        //'goods_supply' => ['character_string1', 'thing6', 'time9', 'name17'],// 订单编号、商品信息、快递单号、发货时间、收货人
        $res = $wxSubMsg->sendMsg($order['user'],[$order['order_no'], $order['goods']['goods_name'], $data['express_no'], time(), $order['receiver_user']],'goods_supply');
    }

    /**
     * 导出订单
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList($params){
        ##获取列表
        $this->setWhere($params);
        ##获取列表
        $list = $this->alias('od')
            ->join('user u','u.user_id = od.user_id','LEFT')
            ->join('user_grade ug','ug.grade_id = u.grade_id','LEFT')
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->with(['image.file']);
                    },
                    'express'
                ]
            )
            ->field(['od.deliver_id', 'od.order_no', 'od.user_id', 'od.goods_id', 'od.goods_num', 'od.address', 'od.receiver_user', 'od.receiver_mobile', 'od.express_id', 'od.express_no', 'od.freight_money', 'od.remark', 'od.create_time', 'od.deliver_type', 'od.deliver_status', 'od.pay_status', 'od.pay_time', 'u.nickName', 'u.avatarUrl', 'u.mobile', 'u.grade_Id', 'ug.name as grade_name', 'od.deliver_time', 'od.complete_time', 'od.transaction_id'])
            ->select();
        return (new Exportservice)->deliveryOrderList2($list);
    }

    /**
     * 获取发货信息
     * @param $goods_sku_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public static function getDeliverInfo($goods_sku_id, $start_time=0, $end_time=0){
        $where = ['goods_sku_id'=>$goods_sku_id];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num = (new self)
            ->where($where)
            ->where(function($query){
                $query->where(
                    [
                        'deliver_type' => 10,
                        'deliver_status' => ['IN', ['20', '40']]
                    ]
                )
                ->whereOr(
                    [
                        'deliver_type' => 20,
                        'deliver_status' => 40
                    ]
                );
            })
            ->sum('goods_num');
        return $num;
    }

    /**
     * 已完成信息
     * @param $goods_sku_id
     * @param int $start_time
     * @param int $end_time
     * @return float|int
     */
    public static function getCompleteInfo($goods_sku_id, $start_time=0, $end_time=0){
        $where = [
            'goods_sku_id'=>$goods_sku_id,
            'deliver_status' => 40
        ];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num = (new self)
            ->where($where)
            ->sum('goods_num');
        return $num;
    }

    /**
     * 待发货信息
     * @param $goods_sku_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public static function getWaitDeliverInfo($goods_sku_id, $start_time, $end_time){
        $where = [
            'goods_sku_id' => $goods_sku_id,
            'pay_status' => 20,
            'deliver_status' => 10,
            'deliver_type' => 10
        ];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        return (new self)->where($where)->sum('goods_num');
    }

    /**
     * 待自提信息
     * @param $goods_sku_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public static function getWaitTakeInfo($goods_sku_id, $start_time, $end_time){
        $where = [
            'goods_sku_id' => $goods_sku_id,
            'pay_status' => 20,
            'deliver_status' => 20,
            'deliver_type' => 20
        ];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        return (new self)->where($where)->sum('goods_num');
    }

    /**
     * 待收货信息
     * @param $goods_sku_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public static function getWaitReceiptInfo($goods_sku_id, $start_time, $end_time){
        $where = [
            'goods_sku_id' => $goods_sku_id,
            'pay_status' => 20,
            'deliver_status' => 20,
            'deliver_type' => 10
        ];
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        return (new self)->where($where)->sum('goods_num');
    }

    /**
     * 提货发货列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDeliverList(){
        $list = (new self)->alias('od')
            ->join('goods g','od.goods_id = g.goods_id','LEFT')
            ->join('goods_sku gs','gs.goods_sku_id = od.goods_sku_id','LEFT')
            ->where([
                'od.pay_status' => 20
            ])
            ->where(function($query){
                $query->where(
                        [
                            'od.deliver_type' => 10,
                            'od.deliver_status' => 10
                        ]
                    )
                    ->whereOr(function($query){
                        $query -> where([
                            'od.deliver_type' => 20,
                            'od.deliver_status' => 20
                        ]);
                    });
            })
            ->field('od.deliver_id,od.goods_id,od.goods_sku_id,od.goods_num,g.goods_name,gs.image_id,gs.spec_sku_id')
            ->select()->toArray();
        return $list;
    }

    /**
     * 用户订单
     * @return array
     * @throws \think\exception\DbException
     */
    public function getUserOrderList(){
        ##参数
        $user_id = input('post.user_id',0,'intval');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $where = [
            'user_id' => $user_id,
            'deliver_status' => ['NEQ', 30],
            'pay_status' => 20
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
                    'stockLog',
                    'user.grade',
                    'goods',
                    'spec.image'
                ]
            )
            ->order('create_time','desc')
            ->paginate(10,false,[
                'type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:ajax_delivery_go([PAGE]);'
            ]);
        $page = $list->render();
        return compact('page','list');
    }

    /**
     * 提货发货运费明细
     * @return array
     * @throws \think\exception\DbException
     */
    public function getDeliveryFreight(){
        ##参数
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $where = [
            'deliver_type' => 10,
            'pay_status' => 20,
            'freight_money' => ['GT', 0]
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
                    'user',
                    'goods',
                    'spec.image'
                ]
            )
            ->order('create_time','desc')
            ->paginate(10,false,[
                'type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:ajax_delivery_go([PAGE]);'
            ]);
        ##总运费
        $total_freight = 0;
        if(!$list->isEmpty())$total_freight = $this->where($where)->sum('freight_money');
        $page = $list->render();
        return compact('page','list','total_freight');
    }

    /**
     * 修改订单物流
     * @param $data
     * @return bool
     */
    public function updateExpress($data){
        if(!isset($data['express_id']) || !$data['express_id'] || !isset($data['express_no']) || !$data['express_no']){
            $this->error = '参数缺失';
            return false;
        }
        return $this->save([
                'express_id' => $data['express_id'],
                'express_no' => $data['express_no']
            ]) !== false;
    }

}