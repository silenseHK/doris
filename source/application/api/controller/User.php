<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use think\Exception;

/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 用户自动登录
     * @return array
     * @throws Exception
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        $userData = $model->login($this->request->post());
        return $this->renderSuccess(array_merge($userData, ['token' => $model->getToken()]));
    }

    /**
     * 168用户注册【停用】
     * @return array
     */
    public function register(){
        try{
            $model = new UserModel;
            return $this->renderSuccess([
                'user_id' => $model->doRegister($this->request->post()),
                'token' => $model->getToken()
            ]);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 当前用户详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 注册发送验证码
     * @return array
     */
    public function sendVerifyCode(){
        try{
            $model = new UserModel;
            $model->sendVerifyCode($this->request->post());
            return $this->renderSuccess('发送成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 绑定手机号
     * @return array
     */
    public function bindMobile(){
        try{
            $user = $this->getUser();
            $model = new UserModel;
            $model->bindMobile($this->request->post(), $user);
            return $this->renderSuccess('','操作成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}
