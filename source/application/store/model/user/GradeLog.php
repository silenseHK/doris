<?php

namespace app\store\model\user;

use app\common\model\user\GradeLog as GradeLogModel;
use app\store\model\User;
use think\db\Query;

/**
 * 用户会员等级变更记录模型
 * Class GradeLog
 * @package app\store\model\user
 */
class GradeLog extends GradeLogModel
{

    /**
     * 新增变更记录
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function record($data)
    {
        return $this->records([$data]);
    }

    /**
     * 获取等级变化列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function logList(){
        $size = input("post.size",10,'intval');
        $this->setLogListWhere();
        $list = $this->alias('l')
            ->join('user u','u.user_id = l.user_id','RIGHT')
            ->field(['u.avatarUrl', 'u.nickname', 'l.new_grade_id as new_grade', 'l.old_grade_id as old_grade', 'l.*'])
            ->with([
                'user' => function(Query $query){
                    $query->field(['user_id', 'nickname', 'grade_id', 'avatarUrl']);
                }
            ])
            ->order('create_time','desc')
            ->paginate($size,false,[
                'query' => \request()->request()
            ]);
        return $list;
    }

    protected function setLogListWhere(){
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        $scene = input('post.scene',0,'intval');
        $search = input('post.search','','search_filter');
        if($search){
            ##获取user_id
            $where['u.nickname'] = ['LIKE', "%{$search}%"];
        }
        if($scene > 0){
            $where['l.change_type'] = $scene;
        }
        if($start_time && $end_time){
            $where['l.create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        if(isset($where))$this->where($where);
    }

}