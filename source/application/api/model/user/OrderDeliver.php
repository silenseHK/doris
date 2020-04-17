<?php


namespace app\api\model\user;
use app\api\model\Goods;
use app\api\model\GoodsSku;
use app\api\model\Order;
use app\api\model\Order as OrderModel;
use app\api\model\Region;
use app\api\model\Setting;
use app\api\model\store\Shop as ShopModel;
use app\api\model\UserAddress;
use app\api\service\Payment;
use app\api\validate\user\OrderDeliverValidate;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\enum\order\PayStatus as PayStatusEnum;
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

    protected $error = '';

    public function __construct($user=null)
    {
        parent::__construct($user);
        $this->user = $user;
        $this->valid = new OrderDeliverValidate();
    }

    /**
     * 初始化创建时间
     * @param $value
     * @return false|string
     */
    public function getCreateTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 初始化支付时间
     * @param $value
     * @return false|string
     */
    public function getPayTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 初始化发货时间
     * @param $value
     * @return false|string
     */
    public function getDeliverTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 初始化完成时间
     * @param $value
     * @return false|string
     */
    public function getCompleteTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
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
        $goodsSkuId = intval($post['goods_sku_id']);
        $goodsNum = intval($post['goods_num']);
        $userId = $this->user['user_id'];
        $deliverType = intval($post['deliver_type']);
        $extract_shop_id = isset($post['shop_id'])? intval($post['shop_id']) : 0;
        if($deliverType == 20 && !$extract_shop_id)throw new Exception('请选择提货门店');
        ##判断库存
        $stock = GoodsStock::getStock($userId, $goodsSkuId);
        if($stock < $goodsNum)throw new Exception('商品库存不足');

        $orderNo = $this->makeOrderNo($deliverType);
        $data = [
            'remark' => strip_tags(isset($post['remark']) ? : ""),
            'deliver_type' => $deliverType,
            'goods_num' => $goodsNum,
            'goods_id' => $goodsId,
            'goods_sku_id' => $goodsSkuId,
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
            if($freight == 0){
                $data['pay_status'] = 20;
                $data['pay_time'] = time();
            }
        }else{ ##自提
            $freight = 0;
            $receiver_mobile = input('post.receiver_mobile','','str_filter');
            $receiver_user = input('post.receiver_user','','str_filter');
            if(!$receiver_mobile || !$receiver_user)throw new Exception('请填写收货人信息');
            $data = array_merge($data, [
                'freight_money' => 0,
                'pay_status' => 20,
                'pay_time' => time(),
                'deliver_status' => 20,
                'deliver_time' => time(),
                'receiver_mobile' => $receiver_mobile,
                'receiver_user' => $receiver_user
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
            'size' => input('size',6,'intval'),
            'user_id' => $user['user_id']
        ];
        $this->setWhere($params);
        ##数据
        $list = $this
            ->with(
                [
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'express' => function(Query $query){
                        $query->field(['express_id', 'express_name', 'express_code']);
                    },
                    'spec' => function(Query $query){
                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with(['image'=>function(Query $query){$query->field(['file_id', 'file_name', 'storage']);}]);
                    }
                ]
            )
            ->page($params['page'], $params['size'])
            ->field(['deliver_id', 'order_no', 'goods_id', 'goods_sku_id', 'goods_num', 'address', 'receiver_user' ,'receiver_mobile', 'express_id', 'express_no', 'freight_money', 'remark', 'create_time', 'deliver_type', 'deliver_status', 'pay_status', 'pay_time', 'deliver_time', 'complete_time'])
            ->order('create_time','desc')
            ->select();
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
            if(GoodsStock::disFreezeStockByUserGoodsId($user['user_id'], $order['goods_sku_id'], $order['goods_num'],1) === false)throw new Exception('操作失败');
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
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'express' => function(Query $query){
                        $query->field(['express_id', 'express_name', 'express_code']);
                    },
                    'extract' => function(Query $query){
                        $query->field(['shop_id', 'shop_name', 'linkman', 'phone', 'address', 'province_id', 'city_id', 'region_id']);
                    },
                    'spec' => function(Query $query){
                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id'])->with(['image'=>function(Query $query){$query->field(['file_id', 'file_name', 'storage']);}]);
                    }
                ]
            )
            ->field(['deliver_id', 'goods_id', 'express_id', 'goods_num', 'address', 'receiver_user', 'receiver_mobile', 'express_id', 'express_no', 'freight_money', 'remark', 'create_time', 'deliver_type', 'deliver_status', 'pay_status', 'pay_time', 'deliver_time', 'complete_time', 'extract_shop_id', 'complete_type', 'goods_sku_id'])
            ->find();
        if(!$order)throw new Exception('订单信息不存在');
        return $order;
    }

    /**
     * 获取提货发货主页信息
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function supply($user){
        ##验证
        $rule = [
            'shop_id' => '>=:0'
        ];
        $res = $this->valid->scene('supply')->rule($rule)->check(request()->get());
        if(!$res)throw new Exception($this->valid->getError());
        ##参数
        $goods_sku_id = input('get.goods_sku_id',0,'intval');
        $shop_id = input('get.shop_id',0,'intval');
        ##数据
        ###自提点
        $extract_shop = $shop_id > 0 ? ShopModel::detail($shop_id) : [];
        ###商品信息
        $goodsSkuModel = new GoodsSku();
        $goods_data = $goodsSkuModel
            ->where(['goods_sku_id'=>$goods_sku_id])
            ->with(
                [
                    'image' => function(Query $query){
                        $query->field(['file_id', 'file_name', 'storage']);
                    },
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual','delivery_id'])->with(['delivery']);
                    }
                ]
            )
            ->field(['goods_id', 'spec_sku_id', 'image_id'])
            ->find();
        $user->hidden(['nickName', 'avatarUrl', 'gender', 'country', 'province', 'city', 'balance', 'withdraw_money', 'freeze_money', 'points', 'pay_money', 'expend_money', 'integral', 'relation', 'invitation_user_id', 'mobile', 'password', 'invitation_code']);

        ##库存
        $stock = UserGoodsStock::getStock($user['user_id'], $goods_sku_id);

        ##判断是否在配送范围
        $city_id = (isset($user['address_default']) && $user['address_default']) ? $user['address_default']['city_id'] : 0;
        $intra_region = false;
        if($city_id) {
            $cityIds = [];
            foreach ($goods_data['goods']['delivery']['rule'] as $item)
                $cityIds = array_merge($cityIds, $item['region_data']);
            if (in_array($city_id, $cityIds))$intra_region = true;
        }

        ##返回模板
        $tid = Setting::getItem('subMsg',10001)['order_deliver']['template_id'];

        return compact('extract_shop','goods_data','user','stock','intra_region','tid');
    }

    /**
     * 物流信息
     * @param $user
     * @return array
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function expressInfo($user){
        ##验证
        if(!$this->valid->scene('express_info')->check(input()))throw new Exception($this->valid->getError());
        ##参数
        $deliver_id = input('get.deliver_id',0,'intval');
        ##订单数据
        $deliver_info = self::get(['deliver_id'=>$deliver_id], ['express']);
        if(!$deliver_info)throw new Exception('订单数据不存在');
        if($deliver_info['pay_status']['value'] != 20 || $deliver_info['deliver_type']['value'] != 10 || $deliver_info['deliver_status']['value'] != 20)throw new Exception('订单不支持此操作');

        ##获取物流信息
        /* @var \app\api\model\Express $model */
        $model = $deliver_info['express'];
        $express = $model->dynamic($model['express_name'], $model['express_code'], $deliver_info['express_no']);
        if ($express === false) {
            throw new Exception($model->getError());
        }
        return compact('express');
    }

    /**
     * 判断当前订单是否允许核销
     * @param static $order
     * @return bool
     */
    public function checkExtractOrder(&$order)
    {
        if (
            $order['pay_status']['value'] == PayStatusEnum::SUCCESS
            && $order['deliver_type']['value'] == DeliveryTypeEnum::EXTRACT
            && $order['deliver_status']['value'] == 20
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    public function setError($err){
        $this->error = $err;
    }

    public function getError(){
        return $this->error;
    }

}