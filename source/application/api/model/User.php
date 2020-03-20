<?php

namespace app\api\model;

use app\common\enum\user\balanceLog\Scene;
use app\common\model\GoodsGrade;
use app\common\model\user\Grade;
use app\common\model\user\IntegralLog;
use app\common\model\UserGoodsStock;
use think\Cache;
use app\common\library\wechat\WxUser;
use app\common\exception\BaseException;
use app\common\model\User as UserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Setting as DealerSettingModel;
use think\Exception;
use think\Hook;
use think\Validate;
use app\common\model\Goods as GoodsModel;
use app\api\validate\user\Check;

/**
 * 用户模型类
 * Class User
 * @package app\api\model
 */
class User extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取用户信息
     * @param $token
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getUser($token)
    {
        $openId = Cache::get($token)['openid'];
        return self::detail(['open_id' => $openId], ['address', 'addressDefault', 'grade']);
    }

    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxlogin($post['code']);
        // 自动注册用户
        $referee_id = isset($post['referee_id']) ? $post['referee_id'] : null;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);
        $user_id = $this->register($session['openid'], $userInfo, $referee_id);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $user_id;
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $wxConfig = Wxapp::getWxappCache();
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['app_id']) || empty($wxConfig['app_secret'])) {
            throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写appid 和 appsecret']);
        }
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxConfig['app_id'], $wxConfig['app_secret']);
        if (!$session = $WxUser->sessionKey($code)) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        return $session;
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        $wxapp_id = self::$wxapp_id;
        // 生成一个不会重复的随机字符串
        $guid = \getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("{$wxapp_id}_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }

    /**
     * 自动注册用户
     * @param $open_id
     * @param $data
     * @param int $referee_id
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function register($open_id, $data, $referee_id = null)
    {
        // 查询用户是否已存在
        $user = self::detail(['open_id' => $open_id]);
        $model = $user ?: $this;
        $data['open_id'] = $open_id;
        $data['wxapp_id'] = self::$wxapp_id;

        // @nickName 用户昵称
        // 此处的preg_replace用于过滤emoji表情
        // 如需支持emoji表情, 需将mysql的编码改为utf8mb4
        $data['nickName'] = preg_replace('/[\xf0-\xf7].{3}/', '', $data['nickName']);

        $this->startTrans();
        try {
            // 保存/更新用户记录
            if (!$model->allowField(true)->save($data)) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 记录推荐人关系
            if (!$user && $referee_id > 0) {
                RefereeModel::createRelation($model['user_id'], $referee_id);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $model['user_id'];
    }

    /**
     * 个人中心菜单列表
     * @return array
     */
    public function getMenus()
    {
        $menus = [
            'address' => [
                'name' => '收货地址',
                'url' => 'pages/address/index',
                'icon' => 'map'
            ],
            'coupon' => [
                'name' => '领券中心',
                'url' => 'pages/coupon/coupon',
                'icon' => 'lingquan'
            ],
            'my_coupon' => [
                'name' => '我的优惠券',
                'url' => 'pages/user/coupon/coupon',
                'icon' => 'youhuiquan'
            ],
            'sharing_order' => [
                'name' => '拼团订单',
                'url' => 'pages/sharing/order/index',
                'icon' => 'pintuan'
            ],
            'my_bargain' => [
                'name' => '我的砍价',
                'url' => 'pages/bargain/index/index?tab=1',
                'icon' => 'kanjia'
            ],
            'dealer' => [
                'name' => '分销中心',
                'url' => 'pages/dealer/index/index',
                'icon' => 'fenxiaozhongxin'
            ],
            'help' => [
                'name' => '我的帮助',
                'url' => 'pages/user/help/index',
                'icon' => 'help'
            ],
        ];
        // 判断分销功能是否开启
        if (DealerSettingModel::isOpen()) {
            $menus['dealer']['name'] = DealerSettingModel::getDealerTitle();
        } else {
            unset($menus['dealer']);
        }
        return $menus;
    }

    /**
     * 代理商品价格和库存
     * @param $user
     * @return array|string
     */
    public static function agentGoodsPriceStock($user){
        try{
            ##验证
            $rule = [
                'goods_id|商品id' => 'require|number|>=:1',
                'num|购买数量' => 'require|number|>=:1'
            ];
            $validate = new Validate($rule);
            $check = $validate->check(input());
            if(!$check)throw new Exception($validate->getError());

            ##接收参数
            $goodsId = input('post.goods_id', 0,'intval');
            $num = input('post.num',1,'intval');

            ##逻辑
            ## 价格
            $agentData = UserModel::getAgentGoodsPriceSupplyUser($user['user_id'], $goodsId, $num);
            ## 检查库存
            $is_stock_enough = 1;
            if(!$agentData['supplyUserId']){
                $is_stock_enough = Goods::checkAgentGoodsStock($goodsId, $num);
            }
            ##返回
            $price = $agentData['price'];
            return compact('price','is_stock_enough');
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 获取再次支付的发货人和最新商品价格
     * @param $userId
     * @param $agentGoods
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function repayGetSupplyGoodsUser($userId, $agentGoods){
        ##计算商品积分
        $diffIntegral = 0;
        $goodsData = [];
        foreach($agentGoods as $k => $v){
            $goodsInfo = GoodsModel::getGoodsAgentInfo($k);
            if($goodsInfo['is_add_integral'] == 1){
                $diffIntegral += $goodsInfo['integral_weight'] * $v;
            }
            if($goodsInfo['sale_type'] == 1){
                $goodsData[$k] = $k;
            }
        }
        ##获取用户当前积分和等级
        $userInfo = self::where(['user_id'=>$userId])->field(['grade_id', 'integral', 'relation'])->find();
        $finalIntegral = $diffIntegral + $userInfo['integral'];
        ##获取最新等级
        $gradeInfo = Grade::getRecentGrade($finalIntegral);
        ##获取供应用户id
        $supplyUserId = self::getSupplyUserId($userInfo['relation'], $gradeInfo['weight']);
        ##获取商品的最新购买价
        if(!empty($goodsData)){
            foreach($goodsData as $k => $v){
                $goodsData[$k] = GoodsGrade::getGoodsPrice($gradeInfo['grade_id'], $k);
            }
        }

        return compact('supplyUserId','goodsData');
    }

    /**
     * 增加积分、返利、出货人余额
     * @param $model
     * @throws Exception
     */
    public static function doIntegralRebate($model){
        ##增加积分
        $diffIntegral = 0;
        ### 获取积分
        foreach($model['goods'] as $goods){
            if($goods['is_add_integral']){
                $diffIntegral += $goods['integral_weight'] * $goods['total_num'];
            }
        }
        $user = $model['user'];
        $oldIntegral = $user['integral'];
        if($diffIntegral > 0){
            self::where(['user_id'=>$user['user_id']])->setInc('integral', $diffIntegral);
            $IntegralModel = (new IntegralLog);
            $IntegralModel->save([
                'user_id' => $user['user_id'],
                'balance_integral' => $oldIntegral,
                'change_integral' => $diffIntegral
            ]);
            $integralLogId = $IntegralModel->getLastInsID();
        }

        ##将货款转到出货人帐下
        $balance = $model['pay_price'] - $model['express_price'];
        self::addBalanceByOrder($model['supply_user_id'], $model['order_id'], $balance, $model['order_no']);

        ##返利
        if($model['rebate_money'] > 0){
            ### 返利给用户
            self::addBalanceByOrder($model['rebate_user_id'], $model['order_id'], $model['rebate_money'], $model['order_no'], Scene::REBATE);
            if($model['supply_user_id'] > 0){
                ### 扣除出货人返利金额
                self::reduceBalanceByOrder($model['supply_user_id'], $model['order_id'], $model['rebate_money'], $model['order_no']);
            }
        }

        ##刷新用户等级
        if($diffIntegral > 0){
            $options = [
                'user_id' => $user['user_id'],
                'integral_log_id' => $integralLogId
            ];
            ### 刷新用户会员等级
            Hook::listen('user_instant_grade',$options);
        }

    }

    public function doRegister($post){
        $Validate = new Check();
        $res = $Validate->scene('register')->check($post);
        if(!$res)throw new Exception($Validate->getError());
    }

}
