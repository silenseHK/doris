<?php


namespace app\store\model\user;

use app\common\model\user\AchievementDetail as AchievementDetailCommon;

class AchievementDetail extends AchievementDetailCommon
{

    public function getSelfList(){
        $this->setSelfListWhere();
        $size = input('post.size',10,'intval');
        $list = $this
            ->with(['user', 'orderInfo'])
            ->field(['id', 'user_id', 'order_id', 'achievement', 'create_time', 'direction', 'remark'])
            ->order('create_time','desc')
            ->paginate($size,false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    public function setSelfListWhere(){
        $user_id = input('post.user_id',0,'intval');
        $month = input('post.month','','str_filter');
        $year = input('post.year','','str_filter');
        $where['user_id'] = $user_id;
        $where['is_add'] = 10;
        if($month && $year){
            $start = mktime(00, 00, 00, date('m', strtotime("${year}-${month}")), 01);
            $end = mktime(23, 59, 59, date('m', strtotime("${year}-${month}"))+1, 00);
            $where['create_time'] = ['BETWEEN', [$start, $end]];
        }
        $this->where($where);
    }

}