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
use think\Exception;


class OrderDeliver extends OrderDeliverModel
{

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
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function apply($post){
        ##验证
        $res = $this->valid->scene(__METHOD__)->check($post);
        if(!$res)throw new Exception($this->valid->getError());
        ##获取参数
        $addressId = intval($post['address_id']);
        $goodsId = intval($post['goods_id']);
        $goodsNum = intval($post['goods_num']);
        $userId = $this->user['user_id'];
        $deliverType = intval($post['deliver_type']);
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
            'order_no' => $orderNo
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
            if($freight > 0)$data['pay_status'] = 20;
        }else{ ##自提
            $freight = 0;
            $data = array_merge($data, [
                'freight_money' => 0,
                'pay_status' => 20
            ]);
        }

        $this->startTrans();
        try{
            ##提交申请
            if($this->isUpdate(false)->allowField(true)->save($data) === false)throw new Exception('提交申请失败');
            ##减库存
            if(GoodsStock::decStockByUserGoodsId($this->user['user_id'], $goodsId, $goodsNum) === false)throw new Exception('提交申请失败');
            ### 添加库存变更记录
            $stockLogData = [
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'balance_stock' => $stock,
                'change_num' => $goodsNum,
                'remark' => '提货发货',
            ];
            if((new UserGoodsStockLog)->isUpdate(false)->save($stockLogData) === false)throw new Exception('提交申请失败');
            $rtn['freight'] = $freight;
            ##支付运费
            if($freight > 0){
                $payment = Payment::freightWechat($this->user, $orderNo, $freight,OrderTypeEnum::FREIGHT);
                $rtn['payment'] = $payment;
            }else{
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
        $addressInfo = UserAddress::detail($this->user['user_id'], $addressId);
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

}