<?php


namespace app\store\service\user;


class Export
{

    protected $salespersonSaleDataTitle = [
        '招商', '部门', '职位', '出货数量', '销售金额'
    ];

    protected $transferDataTitle = [
        'id', '用户id', '昵称', '用户等级', '迁移总库存', '消耗库存量'
    ];

    /**
     * 导出招商业绩
     * @param $list
     * @param $start_time
     * @param $end_time
     */
    public function salespersonSaleData($list, $start_time, $end_time){
        $dataArray = [];
        foreach($list as $item){
            $dataArray[] = [
                '招商' => $this->filterValue($item['name']),
                '部门' => $this->filterValue($item['group']),
                '职位' => $this->filterValue($item['type']),
                '出货数量' => $this->filterValue($item['total_num']),
                '销售金额' => $this->filterValue($item['total_money']),
            ];
        }
        $filename = "招商业绩-{$start_time}-{$end_time}-" . date('YmdHis');
        return export_excel($filename . '.csv', $this->salespersonSaleDataTitle, $dataArray);
    }

    /**
     * 导出迁移老代理明细
     * @param $data
     */
    public function transferData($data, $per){
        $arr = [];
        foreach($data as $k => $list){
            $dataArray = [];
            foreach($list as $key => $item){
                $dataArray[] = [
                    'id' => ($per * $k) + $key + 1,
                    '用户id' => $this->filterNum($item['user_id']),
                    '昵称' => $this->filterValue($item['nickName']),
                    '用户等级' => $this->filterValue($item['grade']['name']),
                    '迁移总库存' => $this->filterNum($item['transfer_stock_data']['transfer_stock_history']),
                    '消耗库存量' => $this->filterNum($item['transfer_stock_data']['transfer_stock_history'] - $item['transfer_stock_data']['transfer_stock']),
                ];
            }
            $arr[] = $dataArray;
        }
        $filename = "老代理迁移明细-" . rand(10000,99999) . date('YmdHis');
        export_excel2($filename . '.csv', $this->transferDataTitle, $arr);
        return true;
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

    private function filterNum($value)
    {
        return intval($value);
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