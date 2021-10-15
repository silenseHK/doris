<?php


namespace app\store\model\user;

use app\common\model\user\Achievement as AchievementCommon;
use think\db\Query;

class Achievement extends AchievementCommon
{

    public function getList(){
        $this->setWhere();
        $size = input('size',15,'intval');
        $list = $this->alias('a')
            ->join('user u','a.user_id = u.user_id','LEFT')
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'grade_id', 'nickName', 'avatarUrl'])->with(['grade'=>function(Query $query){$query->field(['grade_id', 'name']);}]);
                    }
                ]
            )
            ->field(['a.*'])
            ->order(['year'=>'desc', 'month'=>'desc'])
            ->paginate($size,false,[
                'query' => \request()->request()
            ]);
        return $list;
    }

    public function setWhere(){
        $month = input('month','','str_filter');
        $year = input('year','','str_filter');
        $user_id = input('user_id',0,'intval');
        $grade_id = input('scene',0,'intval');
        if($month && $year){
            $where['month'] = $month;
            $where['year'] = $year;
        }
        if($user_id){
            $where['a.user_id'] = $user_id;
        }
        if($grade_id){
            $where['u.grade_id'] = $grade_id;
        }
        if(isset($where))$this->where($where);
    }

    public function getYearList(){
        $this->setGetYearListWhere();
        $size = input('size',15,'intval');
        $list = $this->alias('a')
            ->join('user u','a.user_id = u.user_id','LEFT')
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'grade_id', 'nickName', 'avatarUrl'])->with(['grade'=>function(Query $query){$query->field(['grade_id', 'name']);}]);
                    }
                ]
            )
            ->field('a.user_id, sum(a.self_achievement) as total_self_achievement ,sum(a.team_achievement) as total_team_achievement, min(a.year) as year, min(a.month) as month, min(a.id) as id')
            ->group('a.user_id')
            ->paginate($size,false,[
                'query' => \request()->request()
            ]);
        return $list;
    }

    public function setGetYearListWhere(){
        $year = input('year','','str_filter');
        $user_id = input('user_id',0,'intval');
        $grade_id = input('scene',0,'intval');
        if($user_id){
            $where['a.user_id'] = $user_id;
        }
        if($grade_id){
            $where['u.grade_id'] = $grade_id;
        }
        if($year){
            $where['a.year'] = $year;
        }
        if(isset($where)) $this->where($where);
    }

}