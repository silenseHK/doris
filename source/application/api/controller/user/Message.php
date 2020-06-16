<?php


namespace app\api\controller\user;


use app\api\controller\Controller;
use think\Exception;
use app\api\model\NoticeMessageUser;

class Message extends Controller
{

    /**
     * 通知主页
     * @return array
     */
    public function index(){
        $user = $this->getUser();
        try{
            $model = new NoticeMessageUser();
            return $this->renderSuccess($model->index($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 信息列表
     * @return array
     */
    public function lists(){
        $user = $this->getUser();
        try{
            $model = new NoticeMessageUser();
            return $this->renderSuccess($model->lists($user));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}