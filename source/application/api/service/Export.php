<?php


namespace app\api\service;


class Export
{

    private $tileArray = [
        'openid', '库存'
    ];

    private $saleDataTitleArray = [
        '用户id', '昵称', '电话号码', '省份', '城市', '用户级别', '消费金额', '168库存量', '168正装进货量', '168正装出货量', '用户余额', '直属推荐人', '上级合伙人', '团队人数', '直推人数', '注册时间'
    ];

    private $transferStockRecordTitleArray = [
        '新系统用户id',
        '老系统用户id',
        '电话号码',
        '迁移库存',
        '老系统用户等级',
        '老系统用户余额'
    ];

    /**
     *
     * @param $list
     */
    public function transferStockList($list){
        ##表格内容
        $dataArray = [];
        foreach($list as $item){
            $dataArray[] = [
                'openid' => $this->filterValue($item['openid']),
                '库存' => intval($item['stock']),
            ];
        }
        // 导出csv文件
        $filename = 'transfer-stock-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->tileArray, $dataArray);
    }

    public function exportUserSaleData($list){
        ##表格内容
        $dataArray = [];
        foreach($list as $item){
            $dataArray[] = [
                '用户id' => $this->filterValue($item['userInfo']['user_id']),
                '昵称' => $this->filterValue($item['userInfo']['nickName']),
                '电话号码' => $this->filterValue($item['userInfo']['mobile']),
                '省份' => $this->filterValue($item['userInfo']['province']),
                '城市' => $this->filterValue($item['userInfo']['city']),
                '用户级别' => $this->filterValue($item['userInfo']['grade']['name']),
                '消费金额' => $this->filterValue($item['total_price']),
                '168库存量' => $this->filterValue($item['sale_num']['stock']),
                '168正装进货量' => $this->filterValue($item['total_num']),
                '168正装出货量' => $this->filterValue($item['sale_num']['history_sale']),
                '用户余额' => $this->filterValue($item['userInfo']['balance']),
                '直属推荐人' => isset($item['userInfo']['invitation_user']['nickName'])?$this->filterValue($item['userInfo']['invitation_user']['nickName']):'',
                '上级合伙人' => '',
                '团队人数' => $this->filterValue($item['member_num']),
                '直推人数' => $this->filterValue($item['redirect_member_num']),
                '注册时间' => $this->filterValue($item['userInfo']['create_time'])
            ];
        }
        // 导出csv文件
        $filename = '运营数据-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->saleDataTitleArray, $dataArray);
    }

    public function transferStockRecord($list){
        ##表格内容
        $dataArray = [];
        foreach($list as $item){
            $dataArray[] = [
                '新系统用户id' => $this->filterValue($item['user_id']),
                '老系统用户id' => $this->filterValue($item['old_user_id']),
                '电话号码' => $this->filterValue($item['mobile']),
                '迁移库存' => $this->filterValue($item['transfer_stock_history']),
                '老系统用户等级' => $this->filterValue($item['level']),
                '老系统用户余额' => $this->filterValue($item['money'])
            ];
        }
        // 导出csv文件
        $filename = '老代理库存迁移记录-' . date('YmdHis');
        return export_excel($filename . '.csv', $this->transferStockRecordTitleArray, $dataArray);
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