<?php


namespace app\store\controller\finance\income;

use app\store\controller\Controller;
use app\store\model\PlatformIncomeLog;
use think\Exception;

class Index extends Controller
{

    /**
     * 收入view
     * @return mixed
     */
    public function index(){
        $model = new PlatformIncomeLog();
        $typeList = $model->getTypeList();
        return $this->fetch('', compact('typeList'));
    }

    /**
     * 收入列表
     * @return array|bool
     */
    public function incomeList(){
        try{
            $model = new PlatformIncomeLog();
            return $this->renderSuccess('','', $model->incomeList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function orderInfo(){
        try{
            $model = new PlatformIncomeLog();
            return $this->renderSuccess('','', $model->orderInfo());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}