<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\User;
use app\api\model\user\Fill;
use app\api\model\user\Grade;
use think\Exception;

/**
 * 我的团队
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Team extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $dealer;
    private $setting;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
        // 分销商用户信息
        $this->dealer = DealerUserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 我的团队列表
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($level = -1)
    {
        $model = new RefereeModel;
        return $this->renderSuccess([
            // 分销商用户信息
            'dealer' => $this->dealer,
            // 我的团队列表
            'list' => $model->getList($this->user['user_id'], (int)$level),
            // 基础设置
            'setting' => $this->setting['basic']['values'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 获取等级列表
     * @return array
     */
    public function gradeList(){
        $user = $this->getUser();
        try{
            $model = new Grade();
            return $this->renderSuccess([
                'list' => $model->getGradeList(),
            ]);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 团队列表
     * @return array
     */
    public function memberList(){
        $user = $this->getUser();
        try{
            return $this->renderSuccess($user->getMemberList($this->request->get()));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 团队答卷列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function answerList(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->getAnswerList($user['user_id']));
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 问卷详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function answerDetail(){
        $user = $this->getUser();
        try{
            $model = new Fill();
            return $this->renderSuccess($model->getAnswerDetail());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 普通用户直推团队列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function normalTeamList(){
        $user = $this->getUser();
        try{
            return $this->renderSuccess($user->getNormalTeamList());
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}