<?php


namespace app\api\controller\business;


use app\api\model\business\P_Staff;
use app\api\validate\business\UserValid;

class User extends Base
{

    protected $staffModel;

    protected $validate;

    public function __construct
    (
        UserValid $validate,
        P_Staff $staffModel
    )
    {
        parent::__construct();
        $this->validate = $validate;
        $this->staffModel = $staffModel;
    }

    public function login()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$this->validate->scene('login')->check(request()->post())){
                return $this->renderError($this->validate->getError());
            }
            ##登录验证
            if(!$staff = $this->staffModel->login())
            {
                return $this->renderError($this->staffModel->getError());
            }
            return $this->renderSuccess($staff);
        }
        return false;
    }

    public function logout()
    {
        if(request()->isPost()){
            ##登录验证
            if(!$this->staffModel->logout($this->token))
            {
                return $this->renderError($this->staffModel->getError());
            }
            return $this->renderSuccess('操作成功');
        }
        return false;
    }

    public function managers()
    {
        if(request()->isPost()){
            ##登录验证
            $data = $this->staffModel->managers();
            return $this->renderSuccess($data);
        }
        return false;
    }

}