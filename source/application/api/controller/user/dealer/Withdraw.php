<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\dealer\Withdraw as WithdrawModel;

/**
 * 分销商提现
 * Class Withdraw
 * @package app\api\controller\user\dealer
 */
class Withdraw extends Controller
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
     * 提交提现申请
     * @param $data
     * @return array
     * @throws \app\common\exception\BaseException
     */
    public function submit()
    {
        $formData = $this->request->post();
        $model = new WithdrawModel;
        if ($model->submit($this->user, $formData) === true) {
            return $this->renderSuccess([], '申请提现成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

    /**
     * 分销商提现明细
     * @param int $status
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($status = -1)
    {
        $model = new WithdrawModel;
        return $this->renderSuccess([
            // 提现明细列表
            'list' => $model->getList($this->user['user_id'], (int)$status),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 代理金额信息
     * @return array
     */
    public function index(){
        return $this->renderSuccess($this->user->moneyInfo());
    }

}