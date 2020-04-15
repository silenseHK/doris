<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\Order as OrderModel;
use app\api\model\Setting as SettingModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\common\model\User;

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
        // 订单总数
        $model = new OrderModel;
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
            ]  // 个人中心菜单列表
        ]);
    }

    public function test(){
        $res = User::getAgentGoodsPrice(1,10,1);
        print_r($res);
    }

}
