<?php


namespace app\store\controller\user;


use app\common\enum\user\StockChangeScene;
use app\store\controller\Controller;
use app\store\model\UserGoodsStock;
use app\store\model\UserGoodsStockLog;
use think\Request;

class Goods extends Controller
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function log(){
        $model = new UserGoodsStockLog();
//        print_r($this->model->getLog($this->request->param())->toArray());die;
        return $this->fetch('log',[
            'list' => $model->getLog($this->request->param()),
            'sceneList' => StockChangeScene::data()
        ]);
    }

    public function goodsStock(){
        $model = new UserGoodsStock();
//        print_r($model->getList($this->request->param())->toArray());die;
        return $this->fetch('goods_stock',[
            'list' => $model->getList($this->request->param())
        ]);
    }

}