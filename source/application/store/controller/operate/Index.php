<?php


namespace app\store\controller\operate;


use app\store\controller\Controller;
use app\store\model\OrderGoods;
use think\Exception;

class Index extends Controller
{

    public function userSaleData(){
        return $this->fetch();
    }

    public function exportUserSaleData(){
        try{
            $model = new OrderGoods();
            $model->exportUserSaleData();
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}