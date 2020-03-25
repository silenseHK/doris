<?php

namespace app\api\model;

use app\api\model\user\BalanceLog;
use app\api\model\user\GoodsStock;
use app\api\validate\user\TeamValidate;
use app\common\enum\user\balanceLog\Scene;
use app\common\enum\VerifyCode;
use app\common\model\GoodsGrade;
use app\common\model\MobileVerifyCode;
use app\common\model\user\Grade;
use app\common\model\user\IntegralLog;
use app\common\model\UserGoodsStock;
use think\Cache;
use app\common\library\wechat\WxUser;
use app\common\exception\BaseException;
use app\common\model\User as UserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Setting as DealerSettingModel;
use think\Db;
use think\db\Query;
use think\Exception;
use think\Hook;
use think\Validate;
use app\common\model\Goods as GoodsModel;
use app\api\validate\user\Check;
use app\common\enum\VerifyCode as verifyCodeEnum;
use app\common\model\Setting as SettingModel;

/**
 * 用户模型类
 * Class User
 * @package app\api\model
 */
class User extends UserModel
{

    protected $insert = ['grade_id', 'relation'];

    /**
     * 获取器 -- 设置用户初始会员等级
     * @param $value
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setGradeIdAttr($value){
        return Grade::getLowestGrade()['grade_id'];
    }

    /**
     * 获取器 -- 设置用户的代理关系网
     * @param $value
     * @param $data
     * @return string
     */
    public function setRelationAttr($value, $data){
        if(!isset($data['invitation_user_id']))return "";
        $inviteUserId = $data['invitation_user_id'];
        $relation = $inviteUserId ? (self::getUserRelation($inviteUserId)) : "";
        return $inviteUserId ? "_" . trim($inviteUserId . '_' . trim($relation,'_'),'_') . "_" : "";
    }

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
//        $openId = Cache::get($token)['openid'];
        $mobile = Cache::get($token);
        if(is_array($mobile)){
            $where = ['open_id'=>$mobile['openid']];
        }else{
            $where = ['mobile' => $mobile];
        }
        return self::detail($where, ['address', 'addressDefault', 'grade']);
    }

    /**
     * 用户登录
     * @param $post
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        // 微信登录 获取session_key
        $session = $this->wxlogin($post['code']);
        // 自动注册用户
        $referee_id = isset($post['referee_id']) ? $post['referee_id'] : null;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);
        $userInfo['open_id'] = $session['openid'];
        $userInfo['invitation_user_id'] = intval($referee_id);
        $userData = $this->register($userInfo, $referee_id);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $userData;
    }

    /**
     * 用户登录
     * @param $post
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
//    public function login($post){
//        ##验证
//        $validate = new Check();
//        $rule = [
//            'password|登陆密码' => 'require|min:6|password',
//        ];
//        $res = $validate->scene('login')->rule($rule)->check($post);
//        if(!$res)throw new Exception($validate->getError());
//        ##接收参数
//        $mobile = str_filter($post['mobile']);
//        $password = str_filter($post['password']);
//        $user = $this->where(['mobile'=>$mobile])->field(['user_id', 'password'])->find();
//        if(!$user)throw new Exception('账号或密码错误');
//        if(!password_verify($password, $user['password']))throw new Exception('账号或密码错误');
//        $this->token = $this->token($mobile);
//        // 记录缓存, 7天
//        Cache::set($this->token, $mobile, 86400 * 7);
//        return $user['user_id'];
//    }

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
     * @param $mobile
     * @param $data
     * @param int $referee_id
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function register($data, $referee_id = null)
    {
        // 查询用户是否已存在
        $user = self::detail(['open_id' => $data['open_id']]);
        $model = $user ?: $this;
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
        $is_bind_mobile = 0;
        if($user && $user['mobile'])$is_bind_mobile = 1;
        return ['user_id' => $model['user_id'], 'is_bind_mobile' => $is_bind_mobile];
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

    /**
     * 168 注册
     * @param $post
     * @return mixed
     * @throws BaseException
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function doRegister($post){
        ##验证参数
        $Validate = new Check();
        $res = $Validate->scene('register')->check($post);
        if(!$res)throw new Exception($Validate->getError());
        ##验证手机验证码
        if(!MobileVerifyCode::checkVerifyCode($post['mobile'], $post['verify_code'], verifyCodeEnum::REGISTER))throw new Exception('验证码错误');
        ##判断手机号是否已注册
        if(!self::checkExistMobile($post['mobile']))throw new Exception('手机号已被注册');
        ##微信登录 获取session_key
        $code = str_filter($post['code']);
        $session = $this->wxlogin($code);
        // 自动注册用户
        $referee_id = isset($post['referee_id']) ? $post['referee_id'] : 0;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);
        $userInfo['mobile'] = $post['mobile'];
        $userInfo['open_id'] = $session['openid'];
        $userInfo['password'] = password_hash($post['password'],PASSWORD_DEFAULT);
        $userInfo['invitation_user_id'] = $referee_id;
        $user_id = $this->register($userInfo, $referee_id);

        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $user_id;
    }

    /**
     * 注册发送验证码
     * @param $post
     * @throws Exception
     */
    public function sendVerifyCode($post){
        ##验证参数
        $Validate = new Check();
        $res = $Validate->scene('send_verify_code')->check($post);
        if(!$res)throw new Exception($Validate->getError());
        $mobile = trim($post['mobile']);
        $wxappId = intval($post['wxapp_id']);
        $codeType = intval($post['code_type']);
        if($codeType == VerifyCode::REGISTER){
            ##判断手机号是否已注册
            if(!self::checkExistMobile($mobile))throw new Exception('手机号已经注册过了');
        }
        ##判断发送时间
        MobileVerifyCode::checkSendRight($mobile, $wxappId, $codeType);
        ##发送验证码
        MobileVerifyCode::sendVerifyCode($mobile, $wxappId, $codeType);
    }

    /**
     * 绑定手机号
     * @param $post
     * @param $user
     * @throws Exception
     */
    public function bindMobile($post, $user){
        ##验证参数
        $Validate = new Check();
        $res = $Validate->scene('bind_mobile')->check($post);
        if(!$res)throw new Exception($Validate->getError());
        ##验证验证码
        $mobile = str_filter($post['mobile']);
        $code = str_filter($post['verify_code']);
        if(!MobileVerifyCode::checkVerifyCode($mobile, $code,VerifyCode::REGISTER))throw new Exception('验证码错误');
        ##操作
        Db::startTrans();
        try{
            ##绑定
            $res = $this->where(['user_id'=>$user['user_id']])->setField('mobile', $mobile);
            if($res === false)throw new Exception('手机号绑定失败');
            ##使用验证码
            $res = MobileVerifyCode::useVerifyByMobileCode($mobile, $code);
            if($res === false)throw new Exception('验证码使用失败');
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
        }
    }

    /**
     * 获取提货发货的用户地址和商品库存
     * @param $user
     * @param $post
     * @return array
     * @throws Exception
     */
    public function getGoodsSendData($user, $post){
        ##验证
        $Validate = new Check();
        $res = $Validate->scene('goods_send_data')->check($post);
        if(!$res)throw new Exception($Validate->getError());
        ##获取收货地址
        $address = $user['address_default'] ? : (empty($user['address'])?[] : $user['address'][0]);
        ##商品库存
        $stock = GoodsStock::getStock($user['user_id'], intval($post['goods_id']));
        return compact('address','stock');
    }

    /**
     * 获取可提货发货商品列表
     * @param $user
     * @param $post
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsSendLists($user, $post){
        ##获取用户拥有库存的列表
        return GoodsStock::getSendLists($user['user_id']);
    }

    /**
     * 获取用户成员
     * @param $post
     * @return \think\Paginator
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getMemberList($post){
        ##验证参数
        $Validate = new TeamValidate();
        $res = $Validate->scene('member_list')->check($post);
        if(!$res)throw new Exception($Validate->getError());
        ##接收参数
        $grade_id = intval($post['grade_id']);
        $keywords = isset($post['keywords']) ? str_filter($post['keywords']) : '';
        $page = isset($post['page']) ? intval($post['page']) : 1;
        $size = isset($post['size']) ? intval($post['size']) : 6;
        ##成员列表
        return $this->memberList($this['user_id'], $grade_id, $keywords, $page, $size);
    }

    /**
     * 获取用户成员信息
     * @param $user_id
     * @param $grade_id
     * @param $keywords
     * @param $page
     * @param $size
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function memberList($user_id, $grade_id, $keywords, $page, $size){
        if($keywords)$where['nickName|mobile'] = ['LIKE', "%{$keywords}%"];
        $where['relation'] = ['LIKE', "%_{$user_id}_%"];
        if($grade_id > 0){
            $where['grade_id'] = $grade_id;
        }else{
            ##获取可显示的等级
            $grade_ids = Grade::getShowGradeIds();
            $where['grade_id'] = ['IN', $grade_ids];
        }

        return $this
            ->where($where)
            ->with(['grade' => function(Query $query){
                $query->field(['grade_id', 'name']);
            }])
            ->field(['user_id', 'avatarUrl', 'balance', 'nickName', 'mobile', 'grade_id' ,'user_id as month_buy', 'user_id as day_buy', 'user_id as last_month_buy'])
            ->paginate($size,true, ['page'=>$page]);
    }

    /**
     * 获取器 -- 获取用户本月进货量
     * @param $user_id
     * @return float|int
     */
    public function getMonthBuyAttr($user_id){
        return GoodsStock::countMonthBuy($user_id);
    }

    /**
     * 获取器 -- 获取用户今日进货量
     * @param $user_id
     * @return float|int
     */
    public function getDayBuyAttr($user_id){
        return GoodsStock::countDayBuy($user_id);
    }

    /**
     * 获取器 -- 获取用户上月进货量
     * @param $user_id
     * @return float|int
     */
    public function getLastMonthBuyAttr($user_id){
        return GoodsStock::countLastMonthBuy($user_id);
    }

    /**
     * 冻结待提现的余额
     * @param $money
     * @return bool|string
     */
    public function freezeMoney($money){
        Db::startTrans();
        try{
            $res = $this->save(['freeze_money'=>['inc', $money], 'balance'=> ['dec', $money]]);
            if($res === false)throw new Exception('申请失败');
            ##添加余额变更记录
            $res = BalanceLog::add(Scene::WITHDRAW,[
                'money' => -$money,
                'user_id' => $this['user_id']
            ],'');
            if($res === false)throw new Exception('申请失败');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

}
