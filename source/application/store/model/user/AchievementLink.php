<?php


namespace app\store\model\user;

use app\common\model\user\AchievementLink as AchievementLinkCommon;
use think\db\Query;
use think\Exception;

class AchievementLink extends AchievementLinkCommon
{

    public function getTeamList(){
        $size = input('post.size',15,'intval');
        $achievement_ids = $this->getAchievementIds();
        if(!$achievement_ids)throw new Exception('暂无相关数据');
        $list = $this->where(['achievement_id'=>['IN', $achievement_ids]])->with(['detail'=>function(Query $query){
            $query->with(['user', 'orderInfo']);
        }])->order('create_time','desc')->paginate($size,false, [
            'query' => \request()->request()
        ]);
        return $list;
    }

    public function getAchievementIds(){
        $user_id = input('post.user_id',0,'intval');
        $month = input('post.month','','str_filter');
        $year = input('post.year','','str_filter');
        $where['user_id'] = $user_id;
        if($month && $year){
            $where['year'] = $year;
            $where['month'] = $month;
        }
        $achievement_ids = Achievement::where($where)->column('id');
        return $achievement_ids;
    }

}