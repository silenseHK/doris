<?php


namespace app\api\controller\user;


use app\api\controller\Controller;
use app\api\model\User;
use think\Exception;
use think\Request;

class Agent extends Controller
{
    protected $user;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->user = $this->getUser();
    }

    /**
     * 代理中心
     * @return array
     */
    public function index(){
        try{
            return $this->renderSuccess($this->user->getAgentData());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}