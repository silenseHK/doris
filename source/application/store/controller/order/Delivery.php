<?php


namespace app\store\controller\order;


use app\store\controller\Controller;
use app\store\model\OrderDelivery;
use think\Request;

class Delivery extends Controller
{

    private $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new OrderDelivery();
    }

    /**
     * 导出订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function export(){
        return $this->model->exportList($this->request->param());
    }

}