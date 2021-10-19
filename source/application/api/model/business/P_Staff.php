<?php

namespace app\api\model\business;

use \app\common\model\project\P_Staff as Base_P_Staff;
use think\Cache;
use think\Db;
use think\db\Query;

class P_Staff extends Base_P_Staff
{

    protected $column = 'id, title, a_id, c_id, is_expert, role_id, pwd, login_time, status';

    public function login()
    {
        $account = input('account','');
        $pwd = input('pwd','');
        ##获取用户
        $staff = $this
            ->where('account',$account)
            ->with(
                [
                    'company' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'department' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'role' => function(Query $query)
                    {
                        $query->field('id, title')->with('access');
                    }
                ]
            )
            ->field($this->column)
            ->find();
        if(!$staff){
            return $this->setError('账号或密码错误');
        }
        if($staff['pwd'] != md5($pwd)){
            return $this->setError('账号或密码错误');
        }
        $staff = $staff->toArray();
        unset($staff['pwd']);
        ##生成token
        $staff['token'] = $this->setToken($staff);
        $staff['access'] = array_column($staff['role']['access'],'alias');
        return $staff;
    }

    public function logout($token)
    {
        Cache::clear($token);
        return true;
    }

    public function managers()
    {
        ##获取项目负责人
        $user_ids = Db::name('p_project')->whereNull('delete_time')->group('manager')->column('manager');
        return $this->whereIn('id',$user_ids)->field('id, title')->select();
    }

    protected function setToken($staff)
    {
        ##生成token并存下
        $staff['expire_time'] = 12 * 60 * 60 + time();  //12小时内有效
        $token = $this->makeToken($staff['id']);
        Cache::set($token, $staff);
        return $token;
    }

    protected function makeToken($user_id)
    {
        return md5((string)$user_id . (string)time());
    }

}