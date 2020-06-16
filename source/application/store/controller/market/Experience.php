<?php


namespace app\store\controller\market;


use app\store\controller\Controller;
use app\store\model\GoodsExperience;

class Experience extends Controller
{

    public function rank(){
        return $this->fetch();
    }

    public function orders(){
        return $this->fetch();
    }

    public function getRankList(){
        $model = new GoodsExperience();
        return $this->renderSuccess('','', $model->getRankList());
    }

    public function getOrderList(){
        $model = new GoodsExperience();
        return $this->renderSuccess('','', $model->getOrderList());
    }

}