<?php


namespace app\store\controller\user;


use app\store\controller\Controller;
use app\store\model\UserGoodsStockLog;
use think\Request;

class Goods extends Controller
{

    public $model;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UserGoodsStockLog();
    }

    public function log(){
        return $this->fetch('log',[
            'list' => $this->model->getLog($this->request->param())
        ]);
    }

}