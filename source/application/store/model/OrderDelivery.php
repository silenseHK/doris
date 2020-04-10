<?php


namespace app\store\model;


use app\common\model\user\OrderDeliver;
use app\store\service\order\Export as Exportservice;
use app\store\validate\OrderDeliverValid;
use think\Db;
use think\db\Query;
use think\Exception;
use app\common\service\order\Refund as RefundService;
use app\common\model\UserGoodsStock;

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
                ]
            )
            ->field(['od.deliver_id', 'od.order_no', 'od.user_id', 'od.goods_id', 'od.goods_num', 'od.address', 'od.receiver_user', 'od.receiver_mobile', 'od.express_id', 'od.express_no', 'od.freight_money', 'od.remark', 'od.create_time', 'od.deliver_type', 'od.deliver_status', 'od.pay_status', 'od.pay_time', 'u.nickName', 'u.avatarUrl', 'u.mobile', 'u.grade_Id', 'ug.name as grade_name'])
            ->paginate(10,false,['query' => \request()->request()]);
        return $list;
    }

    /**
     * 设置查询条件
     * @param $params
     */
    public function setWhere($params){
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
        if($model['deliver_status']['value'] != 10){
            throw new Exception('订单不支持确认自提');
        }
        if($model['pay_status']['value'] != 20){
            throw new Exception('订单不支持此操作');
        }
        ##执行操作
        Db:: startTrans();
        try{
            $res = $model->save([
                'deliver_status' => 20,
                'complete_time' => time(),
                'complete_type' => $complete_type
            ]);
            if($res === false)throw new Exception('操作失败');
            ##减少冻结库存
            if(UserGoodsStock::disFreezeStockByUserGoodsId($model['user_id'], $model['goods_id'], $model['goods_num'],1) !== true)throw new Exception('操作失败');
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
        if($model['deliver_status']['value'] != 10){
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
            $res = UserGoodsStock::backStock($model);
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
                'express'
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
        $order = self::get(compact('deliver_id'));
        if($order['pay_status']['value'] != 20)throw new Exception('订单不支持此操作');
        if($order['deliver_type']['value'] != 10)throw new Exception('订单不支持此操作');
        if($order['deliver_status']['value'] != 10)throw new Exception('订单不支持此操作');

        ##更新订单信息
        $data = [
            'express_id' => intval($post['order']['express_id']),
            'express_no' => str_filter($post['order']['express_no']),
            'deliver_status' => 20,
            'deliver_time' => time()
        ];
        $res = $this->isUpdate(true)->save($data, compact('deliver_id'));
        if($res === false)throw new Exception('操作失败');
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
        return (new Exportservice)->deliveryOrderList($list);
    }

}