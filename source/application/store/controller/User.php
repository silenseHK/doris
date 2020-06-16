<?php

namespace app\store\controller;

use app\store\model\User as UserModel;
use app\store\model\user\Grade as GradeModel;
use app\store\model\Goods as GoodsModel;
use app\store\model\UserGoodsStock;
use think\Exception;
use think\Log;

/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param int $user_id 用户id
     * @param string $mobile 手机号
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($nickName = '', $gender = null, $grade = null, $start_time = '', $end_time = '', $user_id = null, $mobile = '')
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender, $grade, $start_time, $end_time, $user_id, $mobile);
        // 会员等级列表
        $gradeList = GradeModel::getUsableList();
        ## 多级代理商品
        $goodsList = GoodsModel::getAgentGoodsList();
        return $this->fetch('index', compact('list', 'gradeList', 'goodsList'));
    }

    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * 用户充值
     * @param $user_id
     * @param int $source 充值类型 1.余额 2.代理商品库存
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function recharge($user_id, $source)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->recharge($this->store['user']['user_name'], $source, $this->postData('recharge'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改会员等级
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function grade($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->updateGrade($this->postData('grade'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取用户代理商品库存
     * @return array
     */
    public function getUserGoodsStock(){
        try{
            ##获取库存
            $stock = UserGoodsStock::getUserGoodsStock();
            if(is_string($stock))throw new Exception($stock);

            return $this->renderSuccess('','',$stock);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}
