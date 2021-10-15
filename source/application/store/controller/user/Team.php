<?php


namespace app\store\controller\user;


use app\store\model\User;
use app\store\model\user\Achievement;
use app\store\model\user\AchievementDetail;
use app\store\model\user\AchievementLink;
use app\store\model\user\ExchangeTeamLog;
use app\store\controller\Controller;
use app\store\model\User as UserModel;
use app\store\model\user\Grade;
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
            return $this->fetch('exchange2');
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
     * 团队转换记录
     * @return array
     */
    public function getExchangeTeamLog(){
        $model = new ExchangeTeamLog();
        return $this->renderSuccess('','',$model->exchangeLog()->toArray());
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

    /**
     * 团队列表
     * @return mixed
     */
    public function teamLists(){
        $grade_list = Grade::getUsableList();
        return $this->fetch('team_lists2',[
            'grade_list' => $grade_list,
            'user_id' => input('user_id',0,'intval')
        ]);
    }

    /**
     * 团队列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getTeamLists(){
        $model = new User();
        return $this->renderSuccess('','',$model->teamLists());
    }

    /**
     * 后台转化成为战略董事
     * @return array|bool
     */
    public function beStrategy(){
        try{
            $model = new UserModel();
            $res = $model->beStrategy();
            if($res !== true)throw new Exception($res);
            return $this->renderSuccess('操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 冻结团队
     * @return array|bool
     */
    public function freeze(){
        try{
            $model = new UserModel();
            $res = $model->freezeTeam();
            if($res !== true)throw new Exception($model->getError());
            return $this->renderSuccess('冻结成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function monthAchievement(){
        return $this->fetch();
    }

    public function getAchievementList(){
        try{
            $model = new Achievement();
            return $this->renderSuccess('','',$model->getList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function achievementDetail($user_id){
        return $this->fetch('', compact('user_id'));
    }

    public function getSelfAchievementDetailList(){
        try{
            $model = new AchievementDetail();
            return $this->renderSuccess('','', $model->getSelfList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function getTeamAchievementDetailList(){
        try{
            $model = New AchievementLink();
            return $this->renderSuccess('','', $model->getTeamList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function yearAchievement(){
        return $this->fetch();
    }

    public function getYearAchievementList(){
        try{
            $model = new Achievement();
            return $this->renderSuccess('','', $model->getYearList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}