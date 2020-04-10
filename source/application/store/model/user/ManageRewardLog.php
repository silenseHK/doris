<?php


namespace app\store\model\user;

use app\common\enum\user\grade\GradeSize;
use app\common\model\user\ManageRewardLog as ManageRewardLogModel;
use app\common\service\ManageReward;
use think\db\Query;
use app\store\model\User;
use think\Exception;

class ManageRewardLog extends ManageRewardLogModel
{

    /**
     * 团队管理奖列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function rewardLog(){
        $this->setWhere(request()->param());
        $list = $this
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'avatarUrl', 'mobile']);
                    },
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual'])->with(['image.file']);
                    }
                ]
            )
            ->paginate(10,false, ['query' => \request()->request()]);
        return $list;
    }

    /**
     * 设置查询筛选条件
     * @param $params
     */
    public function setWhere($params){
        $date = isset($params['date']) && $params['date'] ? str_filter($params['date']) : date('Y-m');
        $where = [
            'date' => $date
        ];
        if(isset($params['search']) && $params['search']){
            $grade_id = Grade::getGradeId(GradeSize::STRATEGY);
            $user_ids = User::where(['nickName|mobile'=>['LIKE',"%{$params['search']}%"], 'grade_id'=>$grade_id])->column('user_id');
            $where['user_id'] = ['IN', $user_ids];
        }
        $this->where($where);
    }

    /**
     * 更新团队管理奖数据
     * @throws \Exception
     */
    public function updateManageData(){
        $ManageReward = new ManageReward();
        $ManageReward->countReward();
        $ManageReward->insertRewardLog();
        if($ManageReward->getError())throw new Exception($ManageReward->getError());
    }

}