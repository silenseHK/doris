<?php


namespace app\store\controller\user;


use app\store\controller\Controller;
use app\store\model\User;
use app\store\model\Goods;
use app\store\model\user\ExchangeStockLog;
use app\store\model\UserGoodsStock as UserGoodsStockStore;
use app\store\model\user\Grade;
use think\Exception;

class Stock extends Controller
{

    /**
     * 库存转移【代理之间】
     * @return mixed
     */
    public function exchange(){
        return $this->fetch();
    }

    /**
     * 用户信息
     * @return array|bool
     */
    public function userInfo(){
        try{
            ##参数
            $user_id = input('post.user_id',0,'intval');
            $data = User::get($user_id);
            if(!$data)throw new Exception('用户不存在');
            if($data['status'] != 1)throw new Exception('用户已冻结');
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 代理商品列表
     * @return array|bool
     */
    public function goodsSkuList(){
        try{
            $model = new Goods();
            $list = $model->getAgentGoodsSkuList();
            return $this->renderSuccess('','',$list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 用户库存
     * @return array|bool
     */
    public function userStock(){
        try{
            $model = new UserGoodsStockStore();
            $data = $model->getUserGoodsStock();
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 代理间转移库存
     * @return array|bool
     */
    public function exchangeStock(){
        try{
            $model = new UserGoodsStockStore();
            if(!$model->exchangeStock()){
                throw new Exception($model->getError());
            }
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 代理间转移库存列表
     * @return array|bool
     */
    public function getExchangeList(){
        try{
            $model = new ExchangeStockLog();
            $data = $model->getExchangeList();
            return $this->renderSuccess('','', $data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 老代理迁移管理
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function transfer(){
        ##等级列表
        $grade_list = Grade::where(['is_delete'=>0, 'status'=>1])->order('upgrade_integral','asc')->field(['grade_id', 'name'])->select();
        $grade_list = $grade_list->isEmpty() ? [] : $grade_list->toArray();
        $transfer_data = UserGoodsStockStore::transferData();
        return $this->fetch('',compact('grade_list','transfer_data'));
    }

    /**
     * 老代理迁移数据
     * @return array|bool
     */
    public function transferUserList(){
        try{
            $model = new User();
            $data = $model->transferUserList();
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 导出迁移代理明细
     * @return array|bool
     */
    public function exportTransferData(){
        try{
            $model = new User();
            $status = $model->exportTransferData();
            if($status === false){
                throw new Exception($model->getError());
            }
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 迁移老代理库存变动明细
     * @return array|bool
     */
    public function userTransferStockLog(){
        try{
            $model = new UserGoodsStockStore();
            $data = $model->userTransferStockLog();
            return $this->renderSuccess('','',$data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}