<?php


namespace app\store\controller\user;


use app\store\model\user\ExchangeTeamLog;
use app\store\controller\Controller;
use app\store\model\User as UserModel;
use app\store\model\user\ManageRewardLog;
use think\Exception;

class Team extends Controller
{

    /**
     * 换团队
     * @return array|bool|mixed
     */
    public function exchange(){
        if(request()->isPost()){
            try{
                $model = new UserModel();
                $res = $model->exchangeTeam();
                if($res !== true)throw new Exception($res);
                return $this->renderSuccess('操作成功');
            }catch(Exception $e){
                return $this->renderError($e->getMessage());
            }
        }else{
            return $this->fetch('exchange');
        }
    }

    /**
     * 团队转换记录
     * @return mixed
     */
    public function exchange_log(){
        $model = new ExchangeTeamLog();
        return $this->fetch('exchange_log', [
            'list' => $model->exchangeLog()
        ]);
    }

    /**
     * 团队管理奖记录
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function manage_reward(){
        $model = new ManageRewardLog();
        return $this->fetch('manage_reward_log', [
            'list' => $model->rewardLog()
        ]);
    }

    /**
     * 更新本月团队管理奖数据
     * @return array|bool
     * @throws \Exception
     */
    public function updateManageData(){
        try{
            $model = new ManageRewardLog();
            $model->updateManageData();
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}