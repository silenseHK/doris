<?php


namespace app\common\library\wechat;


use app\common\model\NoticeMessage;
use app\common\model\Setting;

class WxSubMsg extends WxBase
{

    protected $user;

    protected $temp_list = [];

    protected $config;

    protected $temp_keys = [
        'cash_result' => ['amount1', 'phrase2', 'date3', 'thing4', 'phrase5'],//提现金额、提现方式、申请时间、温馨提示、审核结果
        'sale_success' => ['thing1', 'number2', 'amount3', 'time4', 'thing5'],//商品、数量、金额、卖出时间、备注
        'register_success' => ['name1', 'phone_number2'],//代理姓名、代理手机
        'rebate_income' => ['date1', 'amount2', 'thing4', 'amount5'],//返利时间、返利金额、产品名称、交易金额
        'goods_supply' => ['character_string1', 'thing6', 'character_string5', 'time9', 'name17'],// 订单编号、商品信息、快递单号、发货时间、收货人
        'manage_reward' => ['thing1', 'amount2', 'thing3', 'thing4'],//奖励内容、奖励金额、提现说明、温馨提示
    ];

    public function __construct($appId = null, $appSecret = null)
    {
        parent::__construct($appId, $appSecret);
        $this->config = Setting::getItem('subMsg', 10001);
    }

    /**
     * 获取模板列表
     * @return bool
     * @throws \app\common\exception\BaseException
     */
    public function getTemplateList(){
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$accessToken}";
        $result = $this->get($url);
        $response = $this->jsonDecode($result);
        if (!isset($response['errcode'])) {
            $this->error = 'not found errcode';
            return false;
        }
        if ($response['errcode'] != 0) {
            $this->error = $response['errmsg'];
            return false;
        }
        $this->temp_list = $response['data'];
        return $response['data'];
    }

    public function sendCashCheckResultMsg($user){
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";
        $this->user = $user;
        $amount = '1000元';
        $type = '微信零钱';
        $date = '2019-10-3118:46:00';
        $remark = '提现金额超额';
        $result = '不通过';
        $data = [
            'amount1' => [
                'value' => $amount
            ],
            'phrase2' => [
                'value' => $type
            ],
            'date3' => [
                'value' => $date
            ],
            'thing4' => [
                'value' => $remark
            ],
            'phrase5' => [
                'value' => $result
            ]
        ];
        $params = [
            'touser' => $user['open_id'],
            'template_id' => 'EPvoJ_iu7ZPF5yDpIMHyV0G_Lc4m_I3pmTas5o2lloA',
            'page' => 'index',
            'data' => $data
        ];
        $result = $this->post($url, $this->jsonEncode($params));
        $response = $this->jsonDecode($result);
    }

    /**
     * 发送公用订阅消息
     * @param $message_id
     * @param $user
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function sendCommonMsg($message_id, $user){
        $info = NoticeMessage::get(['id'=>$message_id]);
        $params = $this->filterCommonParam($info, $user);
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";
        $result = $this->post($url, $params);
        $response = $this->jsonDecode($result);
        if (!isset($response['errcode'])) {
            $this->error = 'not found errcode';
            return false;
        }
        if ($response['errcode'] != 0) {
            $this->error = $response['errmsg'];
            return false;
        }
        return true;
    }

    public function filterCommonParam($info, $user){
        $page = $info['url']? : "pages/index/index";
        $pa = $info['params'];
        if($pa){
            $page .= '?';
            $pa = json_decode($pa,true);
            foreach($pa as $k => $v){
                $page .= "{$k}={$v}&";
            }
            $page = trim($page,'&');
        }
        $data = [
            'thing6' => [ //活动名称
                'value' => $info['title']
            ],
            'thing7' => [ //活动内容
                'value' => $info['content']
            ]
        ];
        $template_id = 'BKUWE9eb2uAd2AFt9iuffitK1kMmp8CWKikIlHpsw5I';
        $touser = $user['open_id'];
        $params = compact('touser','template_id','page','data');
        return $this->jsonEncode($params);
    }

    /**
     * 发送订阅消息
     * @param $user
     * @param $values
     * @param $type
     * @return mixed
     * @throws \app\common\exception\BaseException
     */
    public function sendMsg($user, $values, $type){
        $accessToken = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";
        $params = [
            'touser' => $user['open_id'],
            'template_id' => $this->config[$type]['template_id'],
            'page' => $this->config[$type]['page'],
            'data' => $this->makeData($type, $values)
        ];
        $result = $this->post($url, $this->jsonEncode($params));
        $response = $this->jsonDecode($result);
        if (!isset($response['errcode'])) {
            $this->error = 'not found errcode';
            return false;
        }
        if ($response['errcode'] != 0) {
            $this->error = $response['errmsg'];
            return false;
        }
        return true;
    }

    public function makeData($type, $values){
        $fields = $this->temp_keys[$type];
        $data = [];
        foreach($fields as $key => $field){
            $data[$field] = [
                'value' => $values[$key]
            ];
        }
        return $data;
    }

}