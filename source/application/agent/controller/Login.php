<?php


namespace app\agent\controller;


use app\agent\logic\LoginLogic;
use app\common\library\aes\Aes;
use think\Exception;
use think\Request;

class Login extends Base
{

    protected $logic;

    public function __construct(Aes $aes, LoginLogic $loginLogic, Request $request = null)
    {
        parent::__construct($aes, $request);
        $this->logic = $loginLogic;
    }

    public function login(){
        try{
            $res = $this->logic->login();
            if(!$res){
                throw new Exception($this->logic->getError());
            }
            return $this->renderSuccess($res,'登陆成功');
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}