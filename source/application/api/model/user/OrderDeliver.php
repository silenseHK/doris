<?php


namespace app\api\model\user;
use app\api\model\Goods;
use app\api\model\GoodsSku;
use app\api\model\Order as OrderModel;
use app\api\model\Region;
use app\api\model\UserAddress;
use app\api\service\Payment;
use app\api\validate\user\OrderDeliverValidate;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\library\helper;
use app\common\model\user\OrderDeliver as OrderDeliverModel;
use app\common\model\UserGoodsStock;
use app\common\model\UserGoodsStockLog;
use app\common\service\delivery\Express as ExpressService;
use think\Db;
use think\db\Query;
use think\Exception;


class OrderDeliver extends OrderDeliverModel
{

    protected $dateFormat = "U";

    protected $user;

    protected $valid;

    public function __construct($user=null)
    {
        parent::__construct($user);
        $this->user = $user;
        $this->valid = new OrderDeliverValidate();
    }

    /**
     * 提交发货申请
     * @param $post
     * @return string
     * @throws Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function apply($post){
        ##验证
        $res = $this->valid->scene('apply')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##获取参数
        $addressId = intval($post['address_id']);
        $goodsId = intval($post['goods_id']);
        $goodsNum = intval($post['goods_num']);
        $userId = $this->user['user_id'];
        $deliverType = intval($post['deliver_type']);
        $extract_shop_id = isset($post['shop_id'])? intval($post['shop_id']) : 0;
        if($deliverType == 20 && !$extract_shop_id)throw new Exception('请选择提货门店');
        ##判断库存
        $stock = GoodsStock::getStock($userId, $goodsId);
        if($stock < $goodsNum)throw new Exception('商品库存不足');

        $orderNo = $this->makeOrderNo($deliverType);
        $data = [
            'remark' => strip_tags(isset($post['remark']) ? : ""),
            'deliver_type' => $deliverType,
            'goods_num' => $goodsNum,
            'goods_id' => $goodsId,
            'user_id' => $userId,
            'order_no' => $orderNo,
            'extract_shop_id' => $extract_shop_id
        ];
        if($deliverType == 10){  ##发货
            ##判断地址是否有效
            $addressInfo = UserAddress::detail($this->user['user_id'], $addressId);
            if(!$addressInfo)throw new Exception('无效收货地址');
            ##计算运费
            $freightInfo = $this->countFreight($goodsId, $goodsNum, $addressInfo['city_id']);
            if($freightInfo['status'] == 0)throw new Exception($freightInfo['msg']);
            $freight = $freightInfo['freight'];
            $data = array_merge($data, [
                'address' => UserAddress::getAddress($addressInfo),
                'freight_money' => $freight,
                'receiver_mobile' => $addressInfo['phone'],
                'receiver_user' => $addressInfo['name'],
            ]);
            if($freight == 0)$data['pay_status'] = 20;
        }else{ ##自提
            $freight = 0;
            $data = array_merge($data, [
                'freight_money' => 0,
                'pay_status' => 20,
                'deliver_status' => 20,
                'deliver_time' => time()
            ]);
        }

        $this->startTrans();
        try{
            ##提交申请
            if($this->isUpdate(false)->allowField(true)->save($data) === false)throw new Exception('提交申请失败');
            $rtn['freight'] = $freight;
            ##支付运费
            if($freight > 0){
                $payment = Payment::freightWechat($this->user, $orderNo, $freight,OrderTypeEnum::FREIGHT);
                $rtn['payment'] = $payment;
            }else{
                $res = GoodsStock::takeStock(array_merge($data, ['stock'=>$stock]));
                if($res !== true)throw new Exception($res);
                $rtn['payment'] = "";
            }
            $this->commit();
            return $rtn;
        }catch(Exception $e){
            $this->rollback();
            return $e->getMessage();
        }
    }

    /**
     * 计算邮费
     * @param $goodsId
     * @param $num
     * @param $cityId
     * @return array
     */
    public function countFreight($goodsId, $num, $cityId=0){
        $specsId = GoodsSku::where(['goods_id'=>$goodsId])->order('goods_sku_id','asc')->value('goods_sku_id');
        $model = new OrderModel;
        $goodsList = $model->getOrderGoodsListByNow(
            $goodsId,
            $specsId,
            $num,
            $this->user
        );
        $ExpressService = new ExpressService(
            $this->user['wxapp_id'],
            $cityId,
            $goodsList,
            OrderTypeEnum::MASTER
        );
        // 获取不支持当前城市配送的商品
        $notInRuleGoods = $ExpressService->getNotInRuleGoods();
        // 验证商品是否在配送范围
        $intraRegion = $notInRuleGoods === false;
        if ($intraRegion == false) {
            $notInRuleGoodsName = $notInRuleGoods['goods_name'];
            return ['status'=>0,'msg'=>"很抱歉，您的收货地址不在商品 [{$notInRuleGoodsName}] 的配送范围内"];
        } else {
            // 计算配送金额
            $ExpressService->setExpressPrice();
        }
        // 订单总运费金额
        return ['status'=>1,'freight'=>helper::number2($ExpressService->getTotalFreight())];
    }

    /**
     * 获取运费
     * @param $post
     * @return mixed
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getFreight($post){
        ##验证
        $res = $this->valid->scene('count_freight')->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##获取参数
        $addressId = intval($post['address_id']);
        $goodsId = intval($post['goods_id']);
        $goodsNum = intval($post['goods_num']);
        $userId = $this->user['user_id'];
        ##获取地址信息
        $addressInfo = UserAddress::detail($userId, $addressId);
        ##计算运费
        $freightInfo = $this->countFreight($goodsId, $goodsNum, $addressInfo['city_id']);
        if($freightInfo['status'] == 0)throw new Exception($freightInfo['msg']);
        return $freightInfo['freight'];
    }

    /**
     * 获取运费支付订单信息
     * @param $orderNo
     * @return OrderDeliver|null
     * @throws \think\exception\DbException
     */
    public static function getPayDetail($orderNo){
        return self::get(['order_no'=>$orderNo, 'deliver_type'=>10, 'pay_status'=>10], ['user']);
    }

    /**
     * 获取提货发货订单列表
     * @param $user
     * @return false|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderList($user){
        ##验证
        $rule = [
            'deliver_type|出货类型' => 'require|number|in:0,10,20',
        ];
        $res = $this->valid->scene('order_list')->rule($rule)->check(input());
        if(!$res)throw new Exception($this->valid->getError());
        ##参数
        $params = [
            'deliver_type' => input('deliver_type',0,'intval'),
            'order_no' => input('order_no','','str_filter'),
            'deliver_status' => input('deliver_status',0,'intval'),
            'page' => input('page',1,'intval'),
            'size' => input('page',6,'intval'),
            'user_id' => $user['user_id']
        ];
        $this->setWhere($params);
        ##数据
        $list = $this->with(
            [
                'goods' => function(Query $query){
                    $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                },
                'express' => function(Query $query){
                    $query->field(['express_id', 'express_name', 'express_code']);
                }
            ]
        )->page($params['page'], $params['size'])->field(['deliver_id', 'order_no', 'goods_id', 'goods_num', 'address', 'receiver_user' ,'receiver_mobile', 'express_id', 'express_no', 'freight_money', 'remark', 'create_time', 'deliver_type', 'deliver_status', 'pay_status', 'pay_time', 'deliver_time', 'complete_time'])->select();
        return $list;
    }

    /**
     * 设置查询条件
     * @param $params
     */
    public function setWhere($params){
        $where = [];
        if($params['deliver_type'] != 0)$where['deliver_type'] = $params['deliver_type'];
        if($params['order_no'])$where['order_no'] = ['LIKE', "%{$params['order_no']}%"];
        $where['pay_status'] = 20;
        if($params['deliver_status'] != 0)$where['deliver_status'] = $params['deliver_status'];
        $where['user_id'] = $params['user_id'];
        $this->where($where);
    }

    /**
     * 用户确认收货[提货发货的物流订单]
     * @param $user
     * @return bool|string
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function complete($user){
        ##验证
        $res = $this->valid->scene('complete')->check(request()->post());
        if(!$res)throw new Exception($this->valid->getError());
        ##获取订单数据
        $deliver_id = input('post.deliver_id',0,'intval');
        $order = self::get(['deliver_id'=>$deliver_id, 'user_id'=>$user['user_id']]);
        if(!$order)throw new Exception('订单信息不存在');
        if($order['pay_status']['value'] != 20 || $order['deliver_status']['value'] != 20 || $order['deliver_type']['value'] != 10)throw new Exception('该订单不支持此操作');
        Db::startTrans();
        try{
            ##执行确认收货操作
            $data = [
                'complete_type' => 30,
                'complete_time' => time(),
                'deliver_status' => 40
            ];
            $res = $order->isUpdate(true)->save($data);
            if($res === false)throw new Exception('操作失败');
            ##减少冻结的库存
            if(GoodsStock::disFreezeStockByUserGoodsId($user['user_id'], $order['goods_id'], $order['goods_num'],1) === false)throw new Exception('操作失败');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 订单详情
     * @param $user
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($user){
        ##验证
        $res = $this->valid->scene('detail')->check(request()->get());
        if(!$res)throw new Exception($this->valid->getError());
        ##获取订单数据
        $deliver_id = input('get.deliver_id',0,'intval');
        $order = $this
            ->where(['deliver_id'=>$deliver_id, 'user_id'=>$user['user_id'], 'pay_status'=>20])
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual'])->with(['image.file']);
                    },
                    'express' => function(Query $query){
                        $query->field(['express_id', 'express_name', 'express_code']);
                    },
                    'extract' => function(Query $query){
                        $query->field(['shop_id', 'shop_name', 'linkman', 'phone', 'address', 'province_id', 'city_id', 'region_id']);
                    }
                ]
            )
            ->field(['deliver_id', 'goods_id', 'express_id', 'goods_num', 'address', 'receiver_user', 'receiver_mobile', 'express_id', 'express_no', 'freight_money', 'remark', 'create_time', 'deliver_type', 'deliver_status', 'pay_status', 'pay_time', 'deliver_time', 'complete_time', 'extract_shop_id', 'complete_type'])
            ->find();
        if(!$order)throw new Exception('订单信息不存在');
        return $order;
    }

}