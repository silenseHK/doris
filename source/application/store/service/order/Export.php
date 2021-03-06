<?php

namespace app\store\service\order;

use app\store\model\OrderAddress as OrderAddressModel;

/**
 * 订单导出服务类
 * Class Export
 * @package app\store\service\order
 */
class Export
{
    private $tileArray = [
        '订单号', '商品信息', '订单总额', '优惠券抵扣', '积分抵扣', '运费金额', '后台改价', '实付款金额', '支付方式', '下单时间',
        '买家', '买家留言', '配送方式', '自提门店名称', '收货人姓名', '联系电话', '收货人地址', '物流公司', '物流单号',
        '付款状态', '付款时间', '发货状态', '发货时间', '收货状态', '收货时间', '订单状态',
        '微信支付交易号', '是否已评价'
    ];

    private $deliveryTitleArray = [
        '订单号', '商品信息', '运费金额', '下单时间',
        '出货人', '留言', '配送方式', '收货人姓名', '联系电话', '收货人地址', '物流公司', '物流单号',
        '付款状态', '付款时间', '发货时间', '收货时间', '订单状态',
        '微信支付交易号'
    ];

    private $deliveryTitleArray2 = [
        '订单号', '商品信息', '数量',
        '买家', '买家id', '留言', '收货人姓名', '联系电话', '收货人地址', '物流公司', '物流单号',
    ];

    private $withdrawTitleArray = [
        '用户ID', '微信昵称', '手机号',
        '提现金额', '提现方式', '收款信息', '交款方式', '申请时间', '审核时间', '状态'
    ];

    /**
     * 提货发货订单导出
     * @param $list
     */
    public function deliveryOrderList($list){
        ##表格内容
        $dataArray = [];
        foreach($list as $order){
            $dataArray[] = [
                '订单号' => $this->filterValue($order['order_no']),
                '商品信息' => $this->filterDeliveryGoodsInfo($order),
                '运费金额' => $this->filterValue($order['freight_money']),
                '下单时间' => $this->filterValue($order['create_time']),
                '买家' => $this->filterValue($order['user']['nickName']),
                '买家留言' => $this->filterValue($order['remark']),
                '配送方式' => $this->filterValue($order['deliver_type']['text']),
                '收货人姓名' => $this->filterValue($order['receiver_user']),
                '联系电话' => $this->filterValue($order['receiver_mobile']),
                '收货人地址' => $this->filterValue($order['address']),
                '物流公司' => $this->filterValue($order['express']['express_name']),
                '物流单号' => $this->filterValue($order['express_no']),
                '付款状态' => $this->filterValue($order['pay_status']['text']),
                '付款时间' => $this->filterTime($order['pay_time']),
                '发货时间' => $this->filterTime($order['deliver_time']),
                '收货时间' => $this->filterTime($order['complete_time']),
                '订单状态' => $this->filterValue($order['deliver_status']['text']),
                '微信支付交易号' => $this->filterValue($order['transaction_id'])
            ];
        }
        // 导出csv文件
        $filename = 'delivery-order-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->deliveryTitleArray, $dataArray);
    }

    /**
     * 提货发货订单导出
     * @param $list
     */
    public function deliveryOrderList2($list){
        ##表格内容
        $dataArray = [];
        foreach($list as $order){
            $dataArray[] = [
                '订单号' => $this->filterValue($order['order_no']),
                '商品信息' => $this->filterDeliveryGoodsInfo($order),
                '数量' => $this->filterValue($order['goods_num']),
                '买家' => $this->filterValue($order['user']['nickName']),
                '买家用户id' => $this->filterValue($order['user']['user_id']),
                '买家留言' => $this->filterValue($order['remark']),
                '收货人姓名' => $this->filterValue($order['receiver_user']),
                '联系电话' => $this->filterValue($order['receiver_mobile']),
                '收货人地址' => $this->filterValue($order['address']),
                '物流公司' => $this->filterValue($order['express']['express_name']),
                '物流单号' => $this->filterValue($order['express_no'])
            ];
        }
        // 导出csv文件
        $filename = 'delivery-order-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->deliveryTitleArray2, $dataArray);
    }

    /**
     * 订单导出
     * @param $list
     */
    public function orderList($list)
    {
        // 表格内容
        $dataArray = [];
        foreach ($list as $order) {
            /* @var OrderAddressModel $address */
            $address = $order['address'];
            $dataArray[] = [
                '订单号' => $this->filterValue($order['order_no']),
                '商品信息' => $this->filterGoodsInfo($order),
                '订单总额' => $this->filterValue($order['total_price']),
                '优惠券抵扣' => $this->filterValue($order['coupon_money']),
                '积分抵扣' => $this->filterValue($order['points_money']),
                '运费金额' => $this->filterValue($order['express_price']),
                '后台改价' => $this->filterValue("{$order['update_price']['symbol']}{$order['update_price']['value']}"),
                '实付款金额' => $this->filterValue($order['pay_price']),
                '支付方式' => $this->filterValue($order['pay_type']['text']),
                '下单时间' => $this->filterValue($order['create_time']),
                '买家' => $this->filterValue($order['user']['nickName']),
                '买家留言' => $this->filterValue($order['buyer_remark']),
                '配送方式' => $this->filterValue($order['delivery_type']['text']),
                '自提门店名称' => $order['extract_shop_id'] > 0 ? $this->filterValue($order['extract_shop']['shop_name']) : '',
                '收货人姓名' => $this->filterValue($order['address']['name']),
                '联系电话' => $this->filterValue($order['address']['phone']),
                '收货人地址' => $this->filterValue($address ? $address->getFullAddress() : ''),
                '物流公司' => $this->filterValue($order['express']['express_name']),
                '物流单号' => $this->filterValue($order['express_no']),
                '付款状态' => $this->filterValue($order['pay_status']['text']),
                '付款时间' => $this->filterTime($order['pay_time']),
                '发货状态' => $this->filterValue($order['delivery_status']['text']),
                '发货时间' => $this->filterTime($order['delivery_time']),
                '收货状态' => $this->filterValue($order['receipt_status']['text']),
                '收货时间' => $this->filterTime($order['receipt_time']),
                '订单状态' => $this->filterValue($order['order_status']['text']),
                '微信支付交易号' => $this->filterValue($order['transaction_id']),
                '是否已评价' => $this->filterValue($order['is_comment'] ? '是' : '否'),
            ];
        }
        // 导出csv文件
        $filename = 'order-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->tileArray, $dataArray);
    }

    /**
     * 订单导出
     * @param $list
     */
    public function orderList2($list)
    {
        // 表格内容
        $dataArray = [];
        foreach ($list as $order) {
            /* @var OrderAddressModel $address */
            $address = $order['address'];
            $dataArray[] = [
                '订单号' => $this->filterValue($order['order_no']),
                '商品信息' => $this->filterGoodsInfo($order),
                '数量' => $this->filterValue($order['goods'][0]['total_num']),
                '买家' => $this->filterValue($order['user']['nickName']),
                '买家id' => $this->filterValue($order['user']['user_id']),
                '买家留言' => $this->filterValue($order['buyer_remark']),
                '收货人姓名' => $this->filterValue($order['address']['name']),
                '联系电话' => $this->filterValue($order['address']['phone']),
                '收货人地址' => $this->filterValue($address ? $address->getFullAddress() : ''),
                '物流公司' => $this->filterValue($order['express']['express_name']),
                '物流单号' => $this->filterValue($order['express_no'])
            ];
        }
        // 导出csv文件
        $filename = 'order-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->deliveryTitleArray2, $dataArray);
    }

    /**
     * 导出提现申请Excel
     * @param $list
     */
    public function withdrawList($list){
        // 表格内容
        $dataArray = [];
        foreach ($list as $item) {
            /* @var OrderAddressModel $address */
            switch($item['pay_type']['value']){
                case 20:
                    $withdraw_info = "{$item['alipay_name']}|{$item['alipay_account']}";
                    break;
                case 30:
                    $withdraw_info = "{$item['bank_name']}|{$item['bank_account']}|{$item['bank_card']}";
                    break;
                default:
                    $withdraw_info = "";
                    break;
            }
            switch($item['apply_status']){
                case 10:
                    $status_text = "待审核";
                    break;
                case 20:
                    $status_text = "审核通过";
                    break;
                case 30:
                    $status_text = "已驳回";
                    break;
                case 40:
                    $status_text = "已打款";
                    break;
                default:
                    $status_text = "已取消";
            }
            $dataArray[] = [
                '用户ID' => $this->filterValue($item['user_id']),
                '微信昵称' => $this->filterValue($item['nickName']),
                '手机号' => $this->filterValue($item['mobile']),
                '提现金额' => $this->filterValue($item['money']),
                '提现方式' => $this->filterValue($item['pay_type']['text']),
                '收款信息' => $this->filterValue($withdraw_info),
                '交款方式' => $this->filterValue(""),
                '申请时间' => $this->filterValue($item['create_time']),
                '审核时间' => $this->filterValue($item['audit_time'] ?: '--'),
                '状态' => $this->filterValue($status_text)
            ];
        }
        // 导出csv文件
        $filename = 'withdraw-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->withdrawTitleArray, $dataArray);
    }

    /**
     * 批量发货模板
     */
    public function deliveryTpl()
    {
        // 导出csv文件
        $filename = 'delivery-' . date('YmdHis');
        return export_excel($filename . '.csv', ['订单号', '物流单号']);
    }

    /**
     * 格式化商品信息
     * @param $order
     * @return string
     */
    private function filterGoodsInfo($order)
    {
        $content = '';
        foreach ($order['goods'] as $key => $goods) {
            $content .= ($key + 1) . ".商品名称：{$goods['goods_name']}\n";
            !empty($goods['goods_attr']) && $content .= "　商品规格：{$goods['goods_attr']}\n";
            $content .= "　购买数量：{$goods['total_num']}\n";
            $content .= "　商品总价：{$goods['total_price']}元\n\n";
        }
        return $content;
    }

    /**
     * 格式化提货发货商品信息
     * @param $order
     * @return string
     */
    private function filterDeliveryGoodsInfo($order){
        $content = '';
        $goods = $order['goods'];
        $content .= "商品名称：{$goods['goods_name']}\n";
        return $content;
    }

    /**
     * 表格值过滤
     * @param $value
     * @return string
     */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }

    /**
     * 日期值过滤
     * @param $value
     * @return string
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return $this->filterValue(date('Y-m-d H:i:s', $value));
    }

}