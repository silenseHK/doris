<?php


namespace app\store\model\user;
use app\common\model\user\ExchangeTeamLog as ExchangeLogModel;

class ExchangeTeamLog extends ExchangeLogModel
{

    public function exchangeLog(){
        $param = request()->param();
        $this->setWhere($param);
        return $this->alias('log')->join('user', 'user.user_id = log.user_id')->with(['newInvitation', 'oldInvitation'])->order('log.create_time','desc')->paginate(10,false, ['query' => \request()->request()]);
    }

    public function setWhere($params){
        !empty($params['search']) && $this->where('user.nickName', 'like', "%{$params['search']}%");
        // 起始时间
        !empty($params['start_time']) && $this->where('log.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('log.create_time', '<', strtotime($params['end_time']) + 86400);
    }

}