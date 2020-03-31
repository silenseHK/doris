<?php


namespace app\api\model;

use app\common\model\Bank as BankModel;


class Bank extends BankModel
{

    /**
     * 银行列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function banks(){
        return $this->where(['status'=>1])->order('sort','desc')->field(['bank_id', 'bank_name'])->select();
    }

}