<?php


namespace app\store\model;

use app\common\model\OrderRefundLog as OrderRefundLogModel;

class OrderRefundLog extends OrderRefundLogModel
{

    /**
     * 新增
     * @param $data
     * @return false|int
     */
    public static function add($data){
        return (new self)->isUpdate(false)->save($data);
    }

}