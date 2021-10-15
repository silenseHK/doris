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
//        $model = new UserGoodsStockLog();
//        print_r($this->model->getLog($this->request->param())->toArray());die;
        return $this->fetch('log2',[
//            'list' => $model->getLog($this->request->param()),
            'sceneList' => StockChangeScene::data(),
            'user_id' => input('user_id',0,'intval'),
            'goods_id' => input('goods_id',0,'intval'),
            'goods_sku_id' => input('goods_sku_id',0,'intval')
        ]);
    }

    public function getLogList(){
        $model = new UserGoodsStockLog();
        return $this->renderSuccess('','', $model->getLog($this->request->param()));
    }

    public function goodsStock(){
        $model = new UserGoodsStock();
//        print_r($model->getList($this->request->param())->toArray());die;
        return $this->fetch('goods_stock2',[
            'list' => $model->getList($this->request->param())->toArray()
        ]);
    }

}