<?php


namespace app\agent\logic;


use app\agent\model\Agent;
use app\agent\validate\LoginValid;

class LoginLogic extends BaseLogic
{

    protected $valid;

    public function __construct()
    {
        $loginValid = new LoginValid();
        $this->valid = $loginValid;
    }

    /**
     * 登录
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function login(){
        ##验证
        if(!$this->valid->scene('login')->check(request()->post()))return $this->rtnErr($this->valid->getError());
        ##数据
        $mobile = input('post.username','','str_filter');
        $password = input('post.password','','str_filter');
        ##登录
        $password = encrypt_pwd($password);
        $info = Agent::get(compact('mobile','password'), ['user']);
        if(!$info)return $this->rtnErr('账号或密码错误');
        if($info['status'] != 1)return $this->rtnErr('账号已冻结');
        $accessToken = $this->setToken($info);
        return compact('accessToken');
    }

    /**
     * 设置token
     * @param $info
     * @return string
     */
    public function setToken($info){
        $token = $this->token($info['agent_id']);
        $token_expire_time = time() + 7 * 24 * 60 * 60;
        $login_time = time();
        $info->save(compact('token','token_expire_time','login_time'));
        return $token;
    }

}