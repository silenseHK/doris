<?php

namespace app\store\model;

use app\common\enum\user\grade\GradeSize;
use app\common\model\Store as StoreModel;

/**
 * 商城模型
 * Class Store
 * @package app\store\model
 */
class Store extends StoreModel
{
    /* @var Goods $GoodsModel */
    private $GoodsModel;

    /* @var Order $GoodsModel */
    private $OrderModel;

    /* @var User $GoodsModel */
    private $UserModel;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        /* 初始化模型 */
        $this->GoodsModel = new Goods;
        $this->OrderModel = new Order;
        $this->UserModel = new User;
    }

    /**
     * 后台首页数据
     * @return array
     * @throws \think\Exception
     */
    public function getHomeData()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // 最近七天日期
        $lately7days = $this->getLately7days();
        $data = [
            'widget-card' => [
                // 商品总量
                'goods_total' => $this->getGoodsTotal(),
                // 用户总量
                'user_total' => $this->getUserTotal(),
                // 订单总量
                'order_total' => $this->getOrderTotal(),
                // 评价总量
                'comment_total' => $this->getCommentTotal(),
                //总销售金额
                'sale_money_total' => $this->getSaleMoneyTotal(),
                //公司总销售额
                'company_sale_money_total' => $this->getCompanySaleMoneyTotal(),
                //总代理数
                'agent_total' => $this->getAgentTotal(),
                //各个代理数
                'agent_detail' => $this->getAgentDetail()
            ],
            'widget-outline' => [
                // 销售额(元)
                'order_total_price' => [
                    'tday' => $this->getOrderTotalPrice($today),
                    'ytd' => $this->getOrderTotalPrice($yesterday)
                ],
                // 支付订单数
                'order_total' => [
                    'tday' => $this->getOrderTotal($today),
                    'ytd' => $this->getOrderTotal($yesterday)
                ],
                // 新增用户数
                'new_user_total' => [
                    'tday' => $this->getUserTotal($today),
                    'ytd' => $this->getUserTotal($yesterday)
                ],
                // 下单用户数
                'order_user_total' => [
                    'tday' => $this->getPayOrderUserTotal($today),
                    'ytd' => $this->getPayOrderUserTotal($yesterday)
                ]
            ],
            'widget-echarts' => [
                // 最近七天日期
                'date' => json_encode($lately7days),
                'order_total' => json_encode($this->getOrderTotalByDate($lately7days)),
                'order_total_price' => json_encode($this->getOrderTotalPriceByDate($lately7days))
            ]
        ];
        return $data;
    }

    /**
     * 最近七天日期
     */
    private function getLately7days()
    {
        // 获取当前周几
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        return array_reverse($date);
    }

    /**
     * 获取商品总量
     * @return string
     */
    private function getGoodsTotal()
    {
        return number_format($this->GoodsModel->getGoodsTotal());
    }

    /**
     * 获取用户总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    private function getUserTotal($day = null)
    {
        return number_format($this->UserModel->getUserTotal($day));
    }

    /**
     * 获取订单总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    private function getOrderTotal($day = null)
    {
        return number_format($this->OrderModel->getPayOrderTotal($day));
    }

    /**
     * 获取订单总量 (指定日期)
     * @param $days
     * @return array
     * @throws \think\Exception
     */
    private function getOrderTotalByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotal($day);
        }
        return $data;
    }

    /**
     * 获取评价总量
     * @return string
     */
    private function getCommentTotal()
    {
        $model = new Comment;
        return number_format($model->getCommentTotal());
    }

    /**
     * 获取总销售额
     * @return string
     */
    private function getSaleMoneyTotal(){
        $model = new Order;
        return number_format($model->getSaleMoneyTotal());
    }

    /**
     * 获取公司总销售额
     * @return string
     */
    private function getCompanySaleMoneyTotal(){
        $model = new Order;
        return number_format($model->getCompanySaleMoneyTotal());
    }

    /**
     * 获取代理总数
     * @return string
     * @throws \think\Exception
     */
    private function getAgentTotal(){
        $model = new User();
        return number_format($model->getAgentTotal());
    }

    /**
     * 获取各级代理数
     * @return array
     * @throws \think\Exception
     */
    private function getAgentDetail(){
        $model = new User();
        ##推广大使
        $ambassador = $model->getAgentDetail(GradeSize::WEEK);
        ##推广合伙人
        $partner = $model->getAgentDetail(GradeSize::MONTH);
        ##联合创始人
        $founder = $model->getAgentDetail(GradeSize::VIP);
        ##游客
        $visitor = $model->getAgentDetail(GradeSize::VISITOR);

        $data = [
            [
                'name' => '游客',
                'num' => $visitor
            ],
            [
                'name' => '推广大使',
                'num' => $ambassador
            ],
            [
                'name' => '推广合伙人',
                'num' => $partner
            ],
            [
                'name' => '联合创始人',
                'num' => $founder
            ]
        ];
        ##返回
        return $data;
    }

    /**
     * 获取某天的总销售额
     * @param $day
     * @return float|int
     */
    private function getOrderTotalPrice($day)
    {
        return sprintf('%.2f', $this->OrderModel->getOrderTotalPrice($day));
    }

    /**
     * 获取订单总量 (指定日期)
     * @param $days
     * @return array
     */
    private function getOrderTotalPriceByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotalPrice($day);
        }
        return $data;
    }

    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    private function getPayOrderUserTotal($day)
    {
        return number_format($this->OrderModel->getPayOrderUserTotal($day));
    }

}