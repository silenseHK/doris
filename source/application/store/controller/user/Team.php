<?php


namespace app\store\controller\user;


use app\store\model\user\ExchangeTeamLog;
use app\store\controller\Controller;
use app\store\model\User as UserModel;
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

    public function exchange_log(){
        $model = new ExchangeTeamLog();
        return $this->fetch('exchange_log', [
            'list' => $model->exchangeLog()
        ]);
    }

}