<?php


namespace app\agent\controller;


use app\agent\logic\MockLogic;
use app\common\library\aes\Aes;
use think\Exception;
use think\Request;

class Mock extends Base
{

    protected $logic;

    public function __construct(Aes $aes, MockLogic $mockLogic, Request $request = null)
    {
        parent::__construct($aes, $request);
        $this->logic = $mockLogic;
    }

    public function userInfo(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->userInfo($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function logout(){
        return $this->renderSuccess();
    }

    public function verify(){
        return $this->logic->verify();
    }

}