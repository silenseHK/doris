<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\BalanceLog as BalanceLogModel;

/**
 * 余额明细
 * Class Balance
 * @package app\store\controller\user
 */
class Balance extends Controller
{
    /**
     * 余额明细
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function log()
    {
        $model = new BalanceLogModel;
//        print_r($this->request->param());die;
        return $this->fetch('log2', [
            // 充值记录列表
            'list' => $model->getList($this->request->param()),
            // 属性集
            'attributes' => $model::getAttributes(),
            'user_id' => input('user_id',0,'intval')
        ]);
    }

    /**
     * 获取明细记录列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getLogList(){
        $model = new BalanceLogModel;
        return $this->renderSuccess('','',$model->getList($this->request->param()));
    }

}