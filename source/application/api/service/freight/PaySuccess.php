<?php


namespace app\api\service\freight;


use app\api\model\user\GoodsStock;
use app\api\model\user\OrderDeliver;
use app\api\service\Basics;
use app\api\model\User as UserModel;
use app\common\model\UserGoodsStock;
use think\Db;
use think\Exception;

class PaySuccess extends Basics
{

    // 订单模型
    public $model;

    // 当前用户信息
    private $user;

    /**
     * 构造函数
     * PaySuccess constructor.
     * @param $orderNo
     * @throws \think\exception\DbException
     */
    public function __construct($orderNo)
    {
        // 实例化订单模型
        $this->model = OrderDeliver::getPayDetail($orderNo);
        if (!empty($this->model)) {
            $this->wxappId = $this->model['wxapp_id']?:10001;
        }
        // 获取用户信息
        $this->user = UserModel::detail($this->model['user_id']);
    }

    /**
     * 获取订单详情
     * @return OrderDeliver|null
     */
    public function getOrderInfo()
    {
        return $this->model;
    }

    /**
     * 更新运费订单状态
     * @param $payType
     * @param $data
     * @return false|int
     */
    public function onPaySuccess($payType, $data){
        ##更新订单
        $data2 = [
            'pay_time' => time(),
            'transaction_id' => $data['transaction_id'],
            'pay_status' => 20
        ];
        Db::startTrans();
        try{
            $order = $this->model->toArray();
            ##更新库存
            $stock = UserGoodsStock::getStock($this->model['user_id'], $this->model['goods_sku_id']);
            $order['stock'] = $stock;
            $res = GoodsStock::takeStock($order);
            if($res !== true)throw new Exception($res);
            ##更新状态
            $this->model->save($data2);
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $order['error'] = $e->getMessage();
            $order['error_info'] = print_r($e,true);
            log_write($order,'pay-err');
            return $e->getMessage();
        }
    }

}