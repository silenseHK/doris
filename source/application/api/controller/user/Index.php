<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\GoodsExperience;
use app\api\model\Order as OrderModel;
use app\api\model\OrderGoods;
use app\api\model\Qrcode;
use app\api\model\Setting as SettingModel;
use app\api\model\User;
use app\api\model\UserCoupon as UserCouponModel;
use think\Exception;

/**
 * 个人中心主页
 * Class Index
 * @package app\api\controller\user
 */
class Index extends Controller
{
    /**
     * 获取当前用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $user = $this->getUser(false);
        $userModel = new User();
        // 订单总数
        $model = new OrderModel;

        ##待入账金额
        $wait_income_money = 0;
        if($user)
            $wait_income_money = OrderModel::getUserWaitIncomeMoney($user['user_id']);
        $wait_income_money = round($wait_income_money,2);
        return $this->renderSuccess([
            'userInfo' => $user,
            'orderCount' => [
                'payment' => $model->getCount($user['user_id'], 'payment'),
                'received' => $model->getCount($user['user_id'], 'received'),
                'comment' => $model->getCount($user['user_id'], 'comment'),
            ],
            'setting' => [
                'points_name' => SettingModel::getPointsName(),
            ],
            // todo: 废弃
            'couponCount' => (new UserCouponModel)->getCount($user['user_id']),
//            'menus' => $user->getMenus()   // 个人中心菜单列表
            'menus' => $menus = [
                // 'team' => [
                //     'name' => '我的团队',
                //     'url' => 'pages/user/team/team',
                //     'icon' => 'daili'
                // ],
                'address' => [
                    'name' => '收货地址',
                    'url' => 'pages/address/index',
                    'icon' => 'map'
                ],
                'help' => [
                    'name' => '我的帮助',
                    'url' => 'pages/user/help/index',
                    'icon' => 'help'
                ],

            ],  // 个人中心菜单列表
            'message' => $userModel->getMessageNum($user['user_id']),
            'wait_income_money' => $wait_income_money
        ]);
    }

    /**
     * 体验装推荐下单排行榜
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function experienceRankList(){
        // 当前用户信息
//        $user = $this->getUser();
        try{
            $model = new GoodsExperience();
            $list = $model->getExperienceRankList();
            return $this->renderSuccess($list);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 获取体验装群及营养师 二维码
     * @return array
     */
    public function experienceQrCode(){
        try{
            $model = new Qrcode();
            $data = $model->getExperienceQrCode();
            return $this->renderSuccess($data);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}
