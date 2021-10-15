<?php


namespace app\store\controller\order;


use app\common\enum\DeliveryOrderStatus;
use app\store\controller\Controller;
use think\Exception;
use app\store\model\delivery\Order as DeliveryOrder;
use app\store\model\Express as ExpressCompany;

class Express extends Controller
{

    public function lists(){
        $statusList = DeliveryOrderStatus::data();
        return $this->fetch('',compact('statusList'));
    }

    public function statistics(){
        return $this->fetch();
    }

    public function expressList(){
        try{
            $model = new DeliveryOrder();
            $data = $model->expressList();
            if($model->getError()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 物流公司列表
     * @return array|bool
     */
    public function expressCompanyList(){
        try{
            $model = new ExpressCompany();
            return $this->renderSuccess('','', $model->getAll());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 生成电子面单html
     * @return array|bool
     */
    public function expressImage(){
        try{
            $model = new DeliveryOrder();
            $data = $model->expressImage();
            if(!$data){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 电子面单云打印
     * @return array|bool
     */
    public function printOrder(){
        try{
            $model = new DeliveryOrder();
            $data = $model->printOrder();
            if(!$data){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('打印成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 某个订单确认发货
     * @return array|bool
     */
    public function confirmDelivery(){
        try{
            $model = new DeliveryOrder();
            if(!$model->confirmDelivery()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 批量确认发货
     * @return array|bool
     */
    public function batchConfirmDelivery(){
        try{
            $model = new DeliveryOrder();
            if(!$model->batchConfirmDelivery()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 统计数据
     * @return array|bool
     */
    public function statisticsTotal(){
        try{
            $model = new DeliveryOrder();
            return $this->renderSuccess('','', $model->statisticsTotal());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 批量生成面单html
     * @return array|bool
     */
    public function batchExpressImage(){
        try{
            $model = new DeliveryOrder();
            if(!$model->batchExpressImage()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 待生成面单列表
     * @return array|bool
     */
    public function waitExpressImageList(){
        try{
            $model = new DeliveryOrder();
            return $this->renderSuccess('','', $model->waitExpressImageList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 待打印面单列表
     * @return array|bool
     */
    public function waitPrintList(){
        try{
            $model = new DeliveryOrder();
            return $this->renderSuccess('','', $model->waitPrintList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 待确认发货列表
     * @return array|bool
     */
    public function waitConfirmDeliveryList(){
        try{
            $model = new DeliveryOrder();
            return $this->renderSuccess('','', $model->waitConfirmDeliveryList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 取消发货
     * @return array|bool
     */
    public function cancelDelivery(){
        try{
            $model = new DeliveryOrder();
            if(!$model->cancelDelivery()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}