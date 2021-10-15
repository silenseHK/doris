<?php


namespace app\agent\controller;


use app\common\library\aes\Aes;
use think\Exception;
use think\Request;
use app\agent\logic\UserLogic;

class User extends Base
{

    protected $logic;

    public function __construct(Aes $aes, UserLogic $userLogic, Request $request = null)
    {
        parent::__construct($aes, $request);
        $this->logic = $userLogic;
    }

    /**
     * 纵览团队列表
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->lists($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 库存明细
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function stockList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->stockList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 库存信息
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function stock(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->stock());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    public function memberList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->memberList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 直推团队列表
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function redirectMemberList(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->redirectMemberList($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 统计数据
     * @return array|string
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function statistics(){
        $agent = $this->getAgent();
        try{
            return $this->renderSuccess($this->logic->statistics($agent));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}