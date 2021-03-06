<?php

namespace app\store\model;

use app\api\service\Export;
use app\common\model\OrderGoods as OrderGoodsModel;
use think\Exception;

/**
 * 订单商品模型
 * Class OrderGoods
 * @package app\store\model
 */
class OrderGoods extends OrderGoodsModel
{

    /**
     * 获取发货量信息
     * @param $goods_sku_id
     * @return array
     */
    public static function getDeliverInfo($goods_sku_id){
        $total = self::getDeliverNum($goods_sku_id);
        $month = self::getDeliverNum($goods_sku_id, get_month_start_timestamp(), get_month_end_timestamp());
        return compact('total','month');
    }

    /**
     * 发货量[包含已完成的订单]
     * @param $goods_sku_id
     * @param int $start_time
     * @param int $end_time
     * @return float|int
     */
    public static function getDeliverNum($goods_sku_id, $start_time=0, $end_time=0){
        $where = [
            'og.goods_sku_id' => $goods_sku_id
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->where(function($query){
                $query
                    ->where(
                        [
                            'o.delivery_type' => 10,
                            'o.delivery_status' => 20,
                            'o.order_status' => ['IN', [10,30]]
                        ]
                    )
                    ->whereOr(
                        [
                            'o.delivery_type' => 20,
                            'o.delivery_status' => 20,
                            'o.order_status' => 30
                        ]
                    );
            })
            ->sum('og.total_num');
        $num2 = OrderDelivery::getDeliverInfo($goods_sku_id, $start_time, $end_time);
        return $num+$num2;
    }

    /**
     * 获取出货信息
     * @param $goods_sku_id
     * @return array
     */
    public static function getShipInfo($goods_sku_id){
        $total = self::getShipNum($goods_sku_id);
        $month = self::getShipNum($goods_sku_id, get_month_start_timestamp(), get_month_end_timestamp());
        return compact('total','month');
    }

    /**
     * 出货数量
     * @param $goods_sku_id
     * @param int $start_time
     * @param int $end_time
     * @return float|int
     */
    public static function getShipNum($goods_sku_id, $start_time=0, $end_time=0){
        $where = ['og.goods_sku_id'=>$goods_sku_id, 'o.order_status'=>30, 'o.supply_user_id'=>0];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->sum('og.total_num');
        return $num;
    }

    /**
     * 待发货信息
     * @param $goods_sku_id
     * @param int $start_time
     * @param int $end_time
     * @return float|int
     */
    public static function getWaitDeliverInfo($goods_sku_id, $start_time, $end_time){
        $where = [
            'og.goods_sku_id' => $goods_sku_id,
            'o.delivery_type' => 10,
            'o.pay_status' => 20,
            'o.delivery_status' => 10,
            'o.order_status' => 10
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num1 = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->sum('og.total_num');

        $num2 = OrderDelivery::getWaitDeliverInfo($goods_sku_id, $start_time, $end_time);
        return $num1 + $num2;
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
            'og.goods_sku_id' => $goods_sku_id,
            'o.delivery_type' => 20,
            'o.pay_status' => 20,
            'o.delivery_status' => 10,
            'o.order_status' => 10
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num1 = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->sum('og.total_num');

        $num2 = OrderDelivery::getWaitTakeInfo($goods_sku_id, $start_time, $end_time);
        return $num1 + $num2;
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
            'og.goods_sku_id' => $goods_sku_id,
            'o.delivery_type' => 10,
            'o.pay_status' => 20,
            'o.delivery_status' => 20,
            'o.order_status' => 10
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num1 = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->sum('og.total_num');

        $num2 = OrderDelivery::getWaitReceiptInfo($goods_sku_id, $start_time, $end_time);
        return $num1 + $num2;
    }

    /**
     * 已完成信息
     * @param $goods_sku_id
     * @param $start_time
     * @param $end_time
     * @return float|int
     */
    public static function getCompleteInfo($goods_sku_id, $start_time, $end_time){
        $where = [
            'og.goods_sku_id' => $goods_sku_id,
            'o.delivery_status' => 20,
            'o.order_status' => 30
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $num = (new self)->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->sum('og.total_num');
        $num2 = OrderDelivery::getCompleteInfo($goods_sku_id, $start_time, $end_time);
        return $num+$num2;
    }

    public static function getDeliverList(){
        ##获取普通订单[待发货和待自提]
        $list1 = (new self)->alias('og')
            ->join('order o','o.order_id = og.order_id','LEFT')
            ->join('goods_sku gs','gs.goods_sku_id = og.goods_sku_id','LEFT')
            ->where(
                [
                    'o.pay_status' => 20,
                    'o.order_status' => 10,
                    'o.delivery_status' => 10,
                    'o.delivery_type' => 10
                ]
            )
            ->field(['og.goods_id,og.goods_sku_id,og.goods_name,og.total_num as goods_num,gs.image_id,gs.spec_sku_id'])
            ->select()->toArray();
//        print_r($list1);
        ##获取提货发货订单[待发货和自提]
        $list2 = OrderDelivery::getDeliverList();
//        print_r($list2);die;
        $list = [];
        foreach($list1 as $val){
            if(!isset($list[$val['goods_sku_id']])){
                $list[$val['goods_sku_id']] = $val;
            }else{
                $list[$val['goods_sku_id']]['goods_num'] += $val['goods_num'];
            }
        }
        foreach($list2 as $val){
            if(!isset($list[$val['goods_sku_id']])){
                $list[$val['goods_sku_id']] = $val;
            }else{
                $list[$val['goods_sku_id']]['goods_num'] += $val['goods_num'];
            }
        }
        $model = new GoodsSku();
        foreach($list as &$item){
            if(!isset($item['spec_sku_id']) || !$item['spec_sku_id'])
                $spec_attr=[];
            else{
                $spec_arr = explode('_',$item['spec_sku_id']);
                $spec_attr = $model->getSpecList($spec_arr);
            }
            $item['spec_attr'] = $spec_attr;
            $item['image'] = UploadFile::getImage($item['image_id']);
        }
        return $list;
    }

    public function exportUserSaleData(){
        ##参数
        $start_time = input('start_time','','str_filter');
        $end_time = input('end_time','','str_filter');
        $goods_sku_id = input('goods_sku_id',104,'intval');

        $where = [
            'og.goods_sku_id'=>$goods_sku_id,
            'o.pay_status' => 20,
            'o.order_status' => ['IN', [10, 30]]
        ];
        if($start_time && $end_time){
            $where['og.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        $list = $this->alias('og')
            ->join('order o','og.order_id = o.order_id','LEFT')
            ->where($where)
            ->field(['o.total_price', 'o.user_id', 'og.total_num'])
            ->select();
//        print_r($list->toArray());
        $data = [];
        foreach($list as $item){
            if(isset($data[$item['user_id']])){
                $data[$item['user_id']]['total_price'] += $item['total_price'];
                $data[$item['user_id']]['total_num'] += $item['total_num'];
            }else{
                $data[$item['user_id']] = [];
                $data[$item['user_id']]['total_num'] = $item['total_num'];
                $data[$item['user_id']]['total_price'] = $item['total_price'];
            }
        }
        ##用户数据
        $userModel = new User();
        foreach($data as $user_id => &$val){
            $val['userInfo'] = $userModel->where(['user_id'=>$user_id])->field(['user_id', 'nickName', 'mobile', 'province', 'city', 'grade_id', 'balance', 'invitation_user_id', 'create_time'])->with('grade')->find()->toArray();
            ##出货量
            if($val['userInfo']['grade_id'] >= 4){
                $val['sale_num'] = UserGoodsStock::getSaleAndStock($user_id, $goods_sku_id);
            }else{
                $val['sale_num'] = ['history_sale'=>0, 'stock'=>0];
            }
            ##团队人数
            $val['member_num'] = $userModel->getTeamMemberNum($user_id);
            ##直推人数
            $val['redirect_member_num'] = $userModel->where(['invitation_user_id'=>$user_id])->count('user_id');
        }
        if(!$data)throw new Exception('暂无数据');
//        print_r($data);die;
        ##导出数据
        $export = new Export();
        $export->exportUserSaleData($data);
    }

}
