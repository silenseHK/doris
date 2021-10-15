<?php

namespace app\api\model;

use app\api\model\dealer\Withdraw;
use app\api\model\user\BalanceLog;
use app\api\model\user\GoodsStock;
use app\api\model\user\GradeLog;
use app\api\service\Export;
use app\api\validate\user\TeamValidate;
use app\api\validate\user\TransferValid;
use app\common\enum\user\balanceLog\Scene;
use app\common\enum\VerifyCode;
use app\common\model\GoodsGrade;
use app\common\model\MobileVerifyCode;
use app\common\model\NoticeMessage;
use app\common\model\PlatformIncomeLog;
use app\common\model\user\Grade;
use app\common\model\user\IntegralLog;
use app\common\model\UserGoodsStock;
use app\common\service\ManageReward;
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
use app\api\model\user\Grade as ApiGrade;

/**
 * 用户模型类
 * Class User
 * @package app\api\model
 */
class User extends UserModel
{

    protected $insert = ['grade_id', 'relation'];

    /**
     * 迁移对应的等级
     * @var int[]
     */
    protected $transfer_grade = [
        '0' => 1, // 游客 =》 游客
        '1' => 2,  //周体验 =》 周体验
        '2' => 3,  //月体验 =》 月体验
        '3' => 4,  //VIP =》 VIP特约
        '5' => 6,  //经销商 =》 战董
        '6' => 7,  //总代理 =》 董事
        '7' => 1
    ];

    protected $transfer_integral = [
        '0' => 0, // 游客 =》 游客
        '1' => 4,  //周体验 =》 周体验
        '2' => 12,  //月体验 =》 月体验
        '3' => 40,  //VIP =》 VIP特约
        '5' => 1000,  //经销商 =》 战董
        '6' => 3000,  //总代理 =》 董事
        '7' => 0
    ];

    /**
     * 获取器 -- 设置用户初始会员等级
     * @param $value
     * @param $data
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setGradeIdAttr($value, $data){
        if(isset($data['is_transfer']) && $data['is_transfer'])
            return $value;
        else return Grade::getLowestGrade()['grade_id'];
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
        return $inviteUserId ? "-" . trim($inviteUserId . '-' . trim($relation,'-'),'-') . "-" : "";
    }

    private $token;

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'update_time'
    ];

    /**
     * 获取用户信息
     * @param $token
     * @return User|bool|null
     * @throws \think\exception\DbException
     */
    public static function getUser($token)
    {
//        $openId = Cache::get($token)['openid'];
        $mobile = Cache::get($token);
        if(!$mobile)return false;
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
        $login_info = $this->wxlogin($post['code'], $post['encrypted_data'], $post['iv']);
        $session = $login_info['session'];
        // 自动注册用户
        $referee_id = isset($post['referee_id']) ? $post['referee_id'] : null;
        $referee_id = $referee_id?intval($referee_id):null;
        $userInfo = json_decode(htmlspecialchars_decode($post['user_info']), true);
        $userInfo['open_id'] = $session['openid'];
//        $userInfo['union_id'] = $login_info['union_id'];
        $userData = $this->register($userInfo, $referee_id);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        self::where(['user_id'=>$userData['user_id']])->setField(['token'=>$this->token]);
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
     * @param $encrypted_data
     * @param $iv
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code, $encrypted_data, $iv)
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
//        $union_id = $WxUser->unionId($session['session_key'], $encrypted_data, $iv);
        $union_id = '';
        return compact('session','union_id');
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
            if(!$user){
//                throw new BaseException(['msg' => '网络异常，请稍后注册.']);
                ##用户的邀请人
                $invitation_user_id = decode($referee_id)?:0;
                $invitation_user_id = $referee_id?:0;
                ##检查邀请人是否存在
                if($invitation_user_id > 0 && !(self::checkUserExist($invitation_user_id)))$invitation_user_id = 0;
                $data['invitation_user_id'] = intval($invitation_user_id);
            }else{
                $invitation_code = $user['invitation_code'];
            }
            // 保存/更新用户记录
            if (!$model->allowField(true)->save($data)) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            if(!$user){
                $invitation_code = createCode($model['user_id']);
                $model->save(['invitation_code' => $invitation_code]);
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
        return ['user_id' => $model['user_id'], 'is_bind_mobile' => $is_bind_mobile, 'invitation_code' => $invitation_code];
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
//            'coupon' => [
//                'name' => '领券中心',
//                'url' => 'pages/coupon/coupon',
//                'icon' => 'lingquan'
//            ],
//            'my_coupon' => [
//                'name' => '我的优惠券',
//                'url' => 'pages/user/coupon/coupon',
//                'icon' => 'youhuiquan'
//            ],
//            'sharing_order' => [
//                'name' => '拼团订单',
//                'url' => 'pages/sharing/order/index',
//                'icon' => 'pintuan'
//            ],
//            'my_bargain' => [
//                'name' => '我的砍价',
//                'url' => 'pages/bargain/index/index?tab=1',
//                'icon' => 'kanjia'
//            ],
//            'dealer' => [
//                'name' => '分销中心',
//                'url' => 'pages/dealer/index/index',
//                'icon' => 'fenxiaozhongxin'
//            ],
            'help' => [
                'name' => '我的帮助',
                'url' => 'pages/user/help/index',
                'icon' => 'help'
            ],
        ];
        // 判断分销功能是否开启
//        if (DealerSettingModel::isOpen()) {
//            $menus['dealer']['name'] = DealerSettingModel::getDealerTitle();
//        } else {
//            unset($menus['dealer']);
//        }
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
                'goods_sku_id|规格id' => 'require|number|>=:0',
                'num|购买数量' => 'require|number|>=:1'
            ];
            $validate = new Validate($rule);
            $check = $validate->check(input());
            if(!$check)throw new Exception($validate->getError());

            ##接收参数
            $goodsId = input('get.goods_id', 0,'intval');
            $goodsSkuId = input('get.goods_sku_id', 0,'intval');
            $num = input('get.num',1,'intval');

            ##逻辑
            ## 价格
            ##获取购买商品对应的等级
            $grade_info = self::getBuyGoodsGrade2($goodsId, $num);

            ##获取用户等级
            $userInfo = $user;

            if($userInfo['grade']['weight'] >= $grade_info['weight']){
                $grade_id = $userInfo['grade_id'];
            }else{
                $grade_id = $grade_info['grade_id'];
            }

//            $agentData = UserModel::getAgentGoodsPriceSupplyUser($user['user_id'], $goodsId, $num);
            ## 检查库存
            $is_stock_enough = 1;
//            if(!$agentData['supplyUserId']){
//                $is_stock_enough = Goods::checkAgentGoodsStock($goodsSkuId, $num);
//            }
            ##返回
//            $price = $agentData['price'];
            $price = GoodsGrade::getGoodsPrice($grade_id, $goodsId);
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
        $userInfo = self::alias('u')->join('user_grade ug','u.grade_id = ug.grade_id')->where(['u.user_id'=>$userId])->field(['u.grade_id', 'u.integral', 'u.relation', 'ug.weight'])->find();
        $finalIntegral = $diffIntegral + $userInfo['integral'];
        ##获取最新等级
        $gradeInfo = Grade::getRecentGrade($finalIntegral, $userInfo->toArray());
        ##获取供应用户id
        $supplyUserId = self::getSupplyUserId($userInfo['relation'], $gradeInfo['weight']);
        $supplyUserGradeId = $supplyUserId ? User::getUserGrade($supplyUserId) : 0;
        ##获取商品的最新购买价
        if(!empty($goodsData)){
            foreach($goodsData as $k => $v){
                $goodsData[$k] = GoodsGrade::getGoodsPrice($gradeInfo['grade_id'], $k);
            }
        }
        $grade_id = $gradeInfo['grade_id'];
        return compact('supplyUserId','goodsData','supplyUserGradeId','grade_id');
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
                'change_integral' => $diffIntegral,
                'order_id' => $model['order_id']
            ]);
            $integralLogId = $IntegralModel->getLastInsID();
        }

        $noticeMessage = new NoticeMessage();

        ##将货款转到出货人帐下
        $balance = $model['pay_price'] - $model['express_price'];
        if($model['supply_user_id'] > 0){
            self::addBalanceByOrder($model['supply_user_id'], $model['order_id'], $balance, $model['order_no']);
            $noticeMessage->balanceChangeMsg(['order_no'=>$model['order_no'], 'money'=>$balance, 'user_id'=>$model['supply_user_id']],10);
        }else{
            PlatformIncomeLog::addLog([
                'money' => $balance,
                'order_no' => $model['order_no'],
                'type' => 10,
                'direction' => 10,
                'order_type' => 10
            ]);
        }

        if($model['express_price'] > 0){
            PlatformIncomeLog::addLog([
                'money' => $model['express_price'],
                'order_no' => $model['order_no'],
                'type' => 20,
                'direction' => 10,
                'order_type' => 10
            ]);
        }

        ##返利
        if($model['rebate_money'] > 0){
            $rebate_info = $model['rebate_info'];
            foreach($rebate_info as $item){
                ### 返利给用户
                self::addBalanceByOrder($item['user_id'], $model['order_id'], $item['money'], $model['order_no'], Scene::REBATE);
                $noticeMessage->balanceChangeMsg(['order_no'=>$model['order_no'], 'money'=>$item['money'], 'user_id'=>$item['user_id']],20);
            }
            if($model['supply_user_id'] > 0){
                ### 扣除出货人返利金额
                self::reduceBalanceByOrder($model['supply_user_id'], $model['order_id'], $model['rebate_money'], $model['order_no']);
                $noticeMessage->balanceChangeMsg(['order_no'=>$model['order_no'], 'money'=>$model['rebate_money'], 'user_id'=>$model['supply_user_id']],30);
            }else{
                PlatformIncomeLog::addLog([
                    'money' => $model['rebate_money'],
                    'order_no' => $model['order_no'],
                    'type' => 30,
                    'direction' => 20,
                    'order_type' => 10
                ]);
            }
        }

        ##减少用户冻结库存
        if($model['supply_user_id'] > 0){
            foreach($model['goods'] as $goods){
                UserGoodsStock::disFreezeStockByUserGoodsId($model['supply_user_id'], $goods['goods_sku_id'], $goods['total_num'],1);
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
        if(self::checkExistMobile($post['mobile']))throw new Exception('手机号已被注册');
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
            if(self::checkExistMobile($mobile))throw new Exception('手机号已经注册过了');
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
            ##检查是否是迁移用户
            $transfer = $this->where(['mobile'=>$mobile, 'is_transfer'=>1, 'open_id'=>''])->find();
            if($transfer){
                $data = [
                    'open_id' => $user['open_id'],
                    'union_id' => $user['union_id'],
                    'nickName' => $user['nickName'],
                    'avatarUrl' => $user['avatarUrl'],
                    'gender' => $user['gender'],
                    'country' => $user['country'],
                    'province' => $user['province'],
                    'city' => $user['city'],
                    'token' => $user['token'],
                ];
                $res = $this->where(['user_id'=>$transfer['user_id']])->update($data);
                if($res === false)throw new Exception('手机号绑定失败');
                $this->where(['user_id'=>$user['user_id']])->delete();
            }else{
                ##绑定
                $res = $this->where(['user_id'=>$user['user_id']])->setField('mobile', $mobile);
                if($res === false)throw new Exception('手机号绑定失败');
                ##使用验证码
                $res = MobileVerifyCode::useVerifyByMobileCode($mobile, $code);
                if($res === false)throw new Exception('验证码使用失败');
                ##通知邀请人
                if($user['invitation_user_id'] > 0){
                    $noticeMessage = new NoticeMessage();
                    $noticeMessage->newChildRegisterMsg($user);
                }
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
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
        $stock = GoodsStock::getStock($user['user_id'], intval($post['goods_sku_id']));
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
        $where['relation'] = ['LIKE', "%-{$user_id}-%"];
        if($grade_id > 0){
            $where['grade_id'] = $grade_id;
        }
//        else{
//            ##获取可显示的等级
//            $grade_ids = Grade::getShowGradeIds();
//            $where['grade_id'] = ['IN', $grade_ids];
//        }

        return $this
            ->where($where)
            ->with(['grade' => function(Query $query){
                $query->field(['grade_id', 'name']);
            }])
            ->field(['user_id', 'avatarUrl', 'balance', 'nickName', 'mobile', 'grade_id' ,'user_id as month_buy', 'user_id as day_buy', 'user_id as last_month_buy', 'user_id as member_num', 'create_time'])
            ->paginate($size,false, ['page'=>$page]);
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
     * 获取器 -- 下级人数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getMemberNumAttr($user_id){
        return self::where(['relation'=>['LIKE', "%-{$user_id}-%"]])->count('user_id');
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

    /**
     * 代理商中心数据
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentData(){

        ##获取下一级可升级会员等级信息
        $next_grade = ApiGrade::getNextGradeInfo($this['grade']['weight']);
        if($next_grade){
            $grade['rate'] = (int)$this['integral'] .'/'. (int)$next_grade['upgrade_integral'];
            $grade['current'] = (int)$this['integral'];
            $grade['target'] = (int)$next_grade['upgrade_integral'];
            $grade['cha'] = (int)$next_grade['upgrade_integral'] - (int)$this['integral'];
            $grade['next_grade'] = $next_grade['name'];
        }else{
            ##获取最高等级
            $high_grade = ApiGrade::getHighestGradeInfo();
            $grade['rate'] = (int)$high_grade['upgrade_integral'] . '/' . (int)$high_grade['upgrade_integral'];
            $grade['current'] = (int)$high_grade['upgrade_integral'];
            $grade['target'] = (int)$high_grade['upgrade_integral'];
            $grade['cha'] = 0;
            $grade['next_grade'] = '';
        }
        $this['grade']['next'] = $grade;

        ##获取销售量、销售额
        $sale_info = Order::getAgentSaleInfo($this['user_id']);
        $sale_money = $sale_num = 0;
        if(!empty($sale_info)){
            foreach($sale_info as $item){
                $sale_money += $item['pay_price'] - $item['express_price'];
                foreach($item['goods'] as $it){
                    $sale_num += $it['total_num'];
                }
            }
        }
        $this['sale_num'] = $sale_num;
        $this['sale_money'] = $sale_money;

        ##获取推荐人
        $this['invitation_user_info'] = ['mobile' => (self::getUserMobile($this['invitation_user_id'] ? : ''))?:'028-83917116'];

        ##团队人数
        $this['team_member_num'] = $this->getTeamMemberNum($this['user_id']);

        ##获取背景图
        $setting_info = DealerSettingModel::getItem('background');
        $this['setting'] = $setting_info;

        ##待入账
        $this['wait_income_money'] = number_format(Order::getUserWaitIncomeMoney($this['user_id']),2);

        return $this;
    }

    /**
     * 获取用户手机号
     * @param $user_id
     * @return mixed
     */
    public static function getUserMobile($user_id){
        return self::where(['user_id'=>$user_id])->value('mobile');
    }

    /**
     * 获取团队人数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getTeamMemberNum($user_id){
        $like = "%-{$user_id}-%";
        return self::where(['relation'=>['LIKE', $like], 'is_delete'=>0])->count('user_id');
    }

    /**
     * 用户的金额信息
     * @return array
     */
    public function moneyInfo(){
        ##可体现金额
        $can_withdraw_money = $this['balance'];
        ##待入账金额
        $wait_income_money = Order::getUserWaitIncomeMoney($this['user_id']);
        ##待提现金额
        $wait_withdraw_money = Withdraw::getWaitWithDrawMoney($this['user_id']);
        ##已提现金额
        $did_withdraw_money = Withdraw::getDidWithDrawMoney($this['user_id']);
        ##团队管理奖
        $manageReward = new ManageReward();
        $manage_reward = $manageReward->personCountReward($this);
        return compact('can_withdraw_money','wait_income_money','wait_withdraw_money','did_withdraw_money','manage_reward');
    }

    /**
     * 获取用户等级id
     * @param $user_id
     * @return mixed
     */
    public static function getUserGrade($user_id){
        return self::where(compact('user_id'))->value('grade_id');
    }

    /**
     * 绑定邀请人
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindInvitation(){
        if($this['invitation_user_id'])throw new Exception('不能重复绑定推荐人');
        ##检查是否有下级
        if(self::checkExistChild($this['user_id']) > 0)throw new Exception('请联系管理员执行此操作');
        ##参数
        $code = input('post.code','','str_filter');
        if(!$code)throw new Exception('参数缺失');
        $invitation_user_id = decode($code);
        if(!$invitation_user_id)throw new Exception('邀请码错误');
        $invitation_user = self::where(['user_id'=>$invitation_user_id])->field(['user_id', 'relation'])->find();
        if(!$invitation_user)throw new Exception('邀请人不存在');
        $relation = '-' . $invitation_user_id . '-' . trim($invitation_user['relation'],'-') . '-';
        $res = $this->isUpdate(true)->save(compact('invitation_user_id','relation'));
        if($res === false)throw new Exception('邀请人绑定失败');
    }

    /**
     * 检查下级数量
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public static function checkExistChild($user_id){
        return self::where(['relation'=>['LIKE', "%-{$user_id}-%"]])->count('user_id');
    }

    /**
     * 未读信息条数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getMessageNum($user_id){
        return NoticeMessageUser::countDisReadMessage($user_id);
    }

    /**
     * 普通用户团队列表
     * @return \think\Paginator
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function getNormalTeamList(){
        ##验证
        $Validate = new TeamValidate();
        $res = $Validate->scene('normal_team_list')->check(input());
        if(!$res)throw new Exception($Validate->getError());
        ##接收参数
        $params = [
            'grade_id' => input('get.grade_id',0,'intval'),
            'keywords' => input('get.keywords','','search_filter'),
            'page' => input('get.page',1,'intval'),
            'size' => input('get.size',6,'intval'),
            'user_id' => input('get.user_id',0,'intval')
        ];
        $this->setNormalTeamListWhere($params);
        $list = $this
            ->field(['user_id', 'nickName', 'avatarUrl', 'grade_id', 'create_time', 'mobile', 'user_id as redirect_member_num'])
            ->with(
                [
                    'grade' => function(Query $query){
                        $query->field(['grade_id', 'name']);
                    }
                ]
            )
            ->paginate($params['size'],false, ['query'=>\request()->request()]);
        return $list;
    }

    /**
     * 设置普通用户团队筛选条件
     * @param $params
     */
    public function setNormalTeamListWhere($params){
        $user_id = $params['user_id'] ? : $this['user_id'];
        $where['invitation_user_id'] = $user_id;
        if($params['grade_id']){
            $where['grade_id'] = $params['grade_id'];
        }

        if($params['keywords']){
            $where['mobile|nickName'] = ['LIKE', "%{$params['keywords']}%"];
        }

        $this->where($where);
    }

    /**
     * 计算直推下级
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getRedirectMemberNumAttr($user_id){
        return $this->where(['invitation_user_id'=>$user_id])->count();
    }

    /**
     * 获取最新等级
     * @param $level
     * @return int
     */
    public static function transferGrade($level){
        return (new self)->transfer_grade[$level];
    }

    /**
     * 获取迁移用户积分
     * @param $level
     * @return int
     */
    public static function transferIntegral($level){
        return (new self)->transfer_integral[$level];
    }

    /**
     * 迁移代理
     * @return bool
     */
    public function transferAgent(){
        try{
            $this->startTrans();
            ##验证参数
            $valid = new TransferValid();
            if(!$valid->scene('transfer_agent')->rule(input()))throw new Exception($valid->getError());
            $data = [
                'user_id' => 84080,
                'nickName' => input('nickname','','str_filter'),
                'avatarUrl' => input('headimgurl',''),
                'ws_openid' => input('openid',''),
                'invitation_user_id' => 10014,
                'mobile' => input('phone',''),
                'grade_id' => self::transferGrade(input('level',0,'intval')),
                'is_transfer' => 1,
                'wxapp_id' => self::$wxapp_id,
                'sysid' => input('sysid','','str_filter')
            ];
            if(!$this->addTransferUser($data))throw new Exception($this->getError());
            $this->commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加迁移用户
     * @param $data
     * @return bool
     */
    public function addTransferUser($data){
        try{
            ##验证手机号是否存在
            $user = self::get(['mobile'=>$data['mobile']]);
            if($user)throw new Exception('电话号码已存在');
            ##添加用户数据
            if($data['grade_id'] > 1){
                $integralLogModel = new IntegralLog;
                $gradeInfo = Grade::get(['grade_id'=>$data['grade_id']]);
                $data['integral'] = $gradeInfo['upgrade_integral'];
            }
            $res = $this->isUpdate(false)->save($data);
            if($res === false)throw new Exception('操作失败');
            $user_id = $this->getLastInsID();
            $invitation_code = createCode($user_id);
            $this->where(['user_id'=>$user_id])->update(['invitation_code' => $invitation_code]);
            if($data['grade_id'] > 1){
                ##增加积分变更记录
                $integralLogModel->save([
                    'user_id' => $user_id,
                    'balance_integral' => 0,
                    'change_integral' => $gradeInfo['upgrade_integral'],
                    'change_direction' => 10,
                    'change_type' => 10
                ]);
                $integralLogId = $integralLogModel->getLastInsID();
                ##增加升级记录
                $grade_log = [
                    'user_id' => $user_id,
                    'old_grade_id' => 1,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => 10,
                    'remark' => '公众号代理迁移',
                    'change_direction' => 10,
                    'integral_log_id' => $integralLogId
                ];
                (new GradeLog)->recordsOne($grade_log);
            }
            $goods_id = 47;
            $goods_sku_id = 127;

            $stock = input('stock',0,'intval');
            if($stock > 0){
                $res = UserGoodsStock::incTransferAgentStock($user_id, $goods_id, $goods_sku_id, $stock);
                if(is_string($res))throw new Exception($res);
            }
            return true;
        }catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 添加迁移用户
     * @param $data
     * @return bool
     */
    public function addTransferUser2($data){
        try{
            ##验证手机号是否存在
            if($data['mobile'] && $data['mobile'] != 18888888888){
                $user = self::get(['mobile'=>$data['mobile']]);
                if($user)echo "\r\n {$user['mobile']}";
//                if($user)throw new Exception("电话号码已存在{$data['mobile']}");
            }

            ##添加用户数据
            if($data['grade_id'] > 1){
//                $integralLogModel = new IntegralLog;
                $gradeInfo = Grade::get(['grade_id'=>$data['grade_id']]);
                $data['integral'] = $gradeInfo['upgrade_integral'];
            }
            $user_id = $this->insertGetId($data);
            if($user_id === false)throw new Exception('操作失败');
            $invitation_code = createCode($user_id);
            $this->where(['user_id'=>$user_id])->update(['invitation_code' => $invitation_code]);
            return $user_id;
            if($data['grade_id'] > 1){
                ##增加积分变更记录
                $integralLogId = $integralLogModel->insertGetId([
                    'user_id' => $user_id,
                    'balance_integral' => 0,
                    'change_integral' => $gradeInfo['upgrade_integral'],
                    'change_direction' => 10,
                    'change_type' => 10
                ]);
                ##增加升级记录
                $grade_log = [
                    'user_id' => $user_id,
                    'old_grade_id' => 1,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => 10,
                    'remark' => '公众号代理迁移',
                    'change_direction' => 10,
                    'integral_log_id' => $integralLogId
                ];
                (new GradeLog)->recordsOne($grade_log);
            }
            $goods_id = 47;
            $goods_sku_id = 127;

            $stock = input('stock',0,'intval');
            if($stock > 0){
                $res = UserGoodsStock::incTransferAgentStock($user_id, $goods_id, $goods_sku_id, $stock);
                if(is_string($res))throw new Exception($res);
            }
            return true;
        }catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function addTransferUser3($data){

        try{
            ##验证手机号是否存在
            if($data['mobile'] && $data['mobile'] != 18888888888){
                $user = self::get(['mobile'=>$data['mobile']]);
                if($user)echo "\r\n {$user['mobile']}";
//                if($user)throw new Exception("电话号码已存在{$data['mobile']}");
            }
            return;
            ##添加用户数据
//            if($data['grade_id'] > 1){
////                $integralLogModel = new IntegralLog;
//                $gradeInfo = Grade::get(['grade_id'=>$data['grade_id']]);
//                $data['integral'] = $gradeInfo['upgrade_integral'];
//            }
//            $user_id = $this->insertGetId($data);
//            if($user_id === false)throw new Exception('操作失败');
//            $invitation_code = createCode($user_id);
//            $this->where(['user_id'=>$user_id])->update(['invitation_code' => $invitation_code]);
//            return $user_id;
            if($data['grade_id'] > 1){
                ##增加积分变更记录
                $integralLogId = $integralLogModel->insertGetId([
                    'user_id' => $user_id,
                    'balance_integral' => 0,
                    'change_integral' => $gradeInfo['upgrade_integral'],
                    'change_direction' => 10,
                    'change_type' => 10
                ]);
                ##增加升级记录
                $grade_log = [
                    'user_id' => $user_id,
                    'old_grade_id' => 1,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => 10,
                    'remark' => '公众号代理迁移',
                    'change_direction' => 10,
                    'integral_log_id' => $integralLogId
                ];
                (new GradeLog)->recordsOne($grade_log);
            }
            $goods_id = 47;
            $goods_sku_id = 127;

            $stock = input('stock',0,'intval');
            if($stock > 0){
                $res = UserGoodsStock::incTransferAgentStock($user_id, $goods_id, $goods_sku_id, $stock);
                if(is_string($res))throw new Exception($res);
            }
            return true;
        }catch(Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function transferTeam($team){
        $agent_id = input('post.agent_id',0,'intval');
        try{
            $this->startTrans();
            $this->doTransfer($agent_id, $team);
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return false;
        }
    }

    public function doTransfer($invite_user_id, $team){
        foreach($team as $item){
            $data = [
                'nickName' => $item['nickname'],
                'avatarUrl' => $item['headimgurl'],
                'ws_openid' => $item['openid'],
                'invitation_user_id' => $invite_user_id,
                'mobile' => $item['phone'],
                'grade_id' => self::transferGrade($item['level']),
                'is_transfer' => 1,
                'wxapp_id' => self::$wxapp_id,
                'create_time' => time(),
                'update_time' => time(),
                'sysid' => $item['sysid']
            ];
            $data['relation'] = $this->setRelationAttr('',$data);
            $user_id = $this->addTransferUser2($data);
            if(!$user_id)throw new Exception($this->getError());
            if($item['child'])$this->doTransfer($user_id, $item['child']);
        }
        return true;
    }

    public function didTransferTeams($info){
        ##获取父级
        $invitation_user_id = $this->getInvitationUserId($info['recid']);
        if(!$invitation_user_id)throw new Exception("推荐人不存在{$info['openid']}");
        $data = [
            'nickName' => $info['nickname'],
            'avatarUrl' => $info['headimgurl'],
            'ws_openid' => $info['openid'],
            'invitation_user_id' => $invitation_user_id,
            'mobile' => $info['phone'],
            'grade_id' => self::transferGrade($info['level']),
            'integral' => self::transferIntegral($info['level']),
            'is_transfer' => 1,
            'wxapp_id' => self::$wxapp_id,
            'create_time' => time(),
            'update_time' => time(),
            'sysid' => $info['sysid']
        ];
        $data['relation'] = $this->setRelationAttr('',$data);
        $this->addTransferUser3($data);
        return $data;
    }

    public function getInvitationUserId($recid){
        return $this->where(['sysid'=>$recid])->value('user_id');
    }

    public function filterTransfer($team){
        $stock_arr = [];
        $money_arr = [];
        $mobile_arr = [];
        $repeat_mobile_arr = [];
        $rtn = $this->circleFilterTransfer($team, $stock_arr, $money_arr,$mobile_arr,$repeat_mobile_arr);
//        print_r($rtn['money_arr']);die;
//        $user_ids = array_column($rtn['money_arr'], 'user_id');
//        print_r($user_ids);die;
//        echo implode(',',$user_ids);die;
//        print_r($rtn['stock_arr']);die;
        $export = new Export();
        return $export->transferStockList($rtn['stock_arr']);
//        print_r($rtn);die;
//        echo json_encode($rtn);die;
//        print_r($rtn['stock_arr']);die;
    }

    public function filterTransfer2($data){
        $export = new Export();
        return $export->transferStockList($data);
//        print_r($rtn);die;
//        echo json_encode($rtn);die;
    }

    public function circleFilterTransfer($team, &$stock_arr, &$money_arr, &$mobile_arr, &$repeat_mobile_arr){
        foreach($team as $item){
            if(in_array($item['phone'], $mobile_arr) && $item['phone'] != 0){
                $repeat_mobile_arr[] = ['user_id'=>$item['id'], 'stock'=>$item['stock'], 'mobile'=>$item['phone'], 'name'=>$item['name'], 'openid'=>$item['openid']];
            }
            $mobile_arr[] = $item['phone'];
            if($item['stock'] != 0){
                $stock_arr[] = ['user_id'=>$item['id'], 'stock'=>$item['stock'], 'mobile'=>$item['phone'], 'name'=>$item['name'], 'openid'=>$item['openid'], 'level'=>$item['level']];
            }
            if($item['money'] > 0){
                $money_arr[] = ['user_id'=>$item['id'], 'money'=>$item['money'], 'mobile'=>$item['phone'], 'name'=>$item['name']];
            }
            if($item['child']){
                $this->circleFilterTransfer($item['child'], $stock_arr, $money_arr,$mobile_arr,$repeat_mobile_arr);
            }
        }
        return compact('stock_arr','money_arr','repeat_mobile_arr');
    }

    public function transferStockRecord($data){
        $export = new Export();
        return $export->transferStockRecord($data);
    }

}
