<?php


namespace app\common\service\kuaidi100;


class Energy extends Config
{

    private $api_url;  //请求接口
    private $method;  //调用方法名
    private $error = '';  //错误信息
    private $order;  //订单数据
    private $params; //请求参数
    private $t;  //时间戳
    private $sign;  //签名

    public function __construct($type, $order)
    {
        parent::__construct();
        $this->method = $type;
        $this->order = $order;
    }

    public function task(){
        if(!in_array($this->method, $this->methods)){
            $this->error = '方法不存在';
            return false;
        }

        if(!method_exists($this, $this->method)){
            $this->error = '方法不存在.';
            return false;
        }

        $this->api_url = $this->url[$this->method];

        return call_user_func([$this, $this->method]);
    }

    protected function getPrintImg(){
        $this->initParams();
        $this->makeSign();
        $params = json_encode($this->params);
        $this->api_url = $this->api_url . "&key={$this->key}&sign={$this->sign}&t={$this->t}&param={$params}";
        $res = $this->curl($this->api_url);
        var_dump($res);
    }

    protected function getElecOrder(){
        $this->initElecOrderParams();
        $this->makeSign();
        $data = [
            'method' => 'getElecOrder',
            'key' => $this->key,
            'sign' => $this->sign,
            't' => $this->t,
            'param' => json_encode($this->params)
        ];
        $res = $this->curlPost($this->api_url, $data);
        if(!$res){
            $this->error = '面单生成请求失败';
            return false;
        }
        $res = json_decode($res, true);
        if($res['status'] != 200){
            $this->error = $res['message'];
            return false;
        }
        return $res['data'];
    }

    public function printOrder(){
        $this->initPrintOrderParams();
        $this->makeSign();
//        print_r($this->params);die;
//        print_r($this->sign);die;
        $params = json_encode($this->params);
        $this->api_url = $this->api_url . "&key={$this->key}&sign={$this->sign}&t={$this->t}&param={$params}";
//        echo $this->api_url;die;
        $res = $this->curl($this->api_url);
        var_dump($res);
    }

    public function eOrder(){
        $this->initEOrderParams();
        $this->makeSign();
        $params = json_encode($this->params);
        $this->api_url = $this->api_url . "&key={$this->key}&sign={$this->sign}&t={$this->t}&param={$params}";
        $res = $this->curl($this->api_url);
        if(!$res){
            $this->error = '打印面单请求失败';
            return false;
        }
        $res = json_decode($res, true);
        if($res['status'] != 200){
            $this->error = $res['message'];
            return false;
        }
        return $res['data'];
    }

    public function printOld(){
        $this->initPrintOldParams();
        $this->makeSign();
        $params = json_encode($this->params);
        $this->api_url = $this->api_url . "&key={$this->key}&sign={$this->sign}&t={$this->t}&param={$params}";
        $res = $this->curl($this->api_url);
        if(!$res){
            $this->error = '打印面单请求失败';
            return false;
        }
        $res = json_decode($res, true);
        if($res['returnCode'] != 200){
            $this->error = $res['message'];
            return false;
        }
        return $res['data'];
    }

    protected function initParams(){
        $params = [
            'type' => 10,
            'partnerId' => $this->partnerId,
            'partnerKey' => $this->partnerKey,
            'kuaidicom' => $this->order['express_code'],
            'recManName' => $this->order['receive_user'],
            'recManMobile' => $this->order['receive_mobile'],
            'recManPrintAddr' => $this->order['receive_address'],
            'sendManName' => $this->order['send_user'],
            'sendManMobile' => $this->order['send_mobile'],
            'sendManPrintAddr' => $this->order['send_address'],
            'tempid' => $this->tempIds[$this->order['express_code']],
            'cargo' => $this->order['goods_full_name'],
            'count' => $this->order['goods_num'],
            'payType' => 'SHIPPER',
            'expType' => '标准快递',
            'remark' => $this->order['remark']
        ];
        $this->params = $params;
    }

    protected function initElecOrderParams(){
        $params = [
            'partnerId' => $this->partnerId,
            'partnerKey' => $this->partnerKey,
            'kuaidicom' => $this->order['express_code'],
            'recMan' => [
                'name' => $this->order['receive_user'],
                'mobile' => $this->order['receive_mobile'],
                'printAddr' => $this->order['receive_address'],
            ],
            'sendMan' => [
                'name' => $this->order['send_user'],
                'mobile' => $this->order['send_mobile'],
                'printAddr' => $this->order['send_address'],
            ],
            'cargo' => $this->order['goods_full_name'],
            'count' => $this->order['goods_num'],
            'payType' => 'SHIPPER',
            'expType' => '标准快递',
            'remark' => $this->order['remark'],
//            'needTemplate' => 1
        ];
        $this->params = $params;
    }

    protected function initPrintOrderParams(){
        $params = [
            'orderId' => $this->order['order_no'],
            'tempid' => $this->tempIds[$this->order['express_code']],
            'siid' => $this->siid,
            'callBackUrl' => "https://casc168.dekichina.com/callback.php"
        ];
        $this->params = $params;
    }

    protected function initEOrderParams(){
        $params = [
            'type' => 10,
            'partnerId' => $this->partnerId,
            'partnerKey' => $this->partnerKey,
            'kuaidicom' => $this->order['express_code'],
            'recMan' => [
                'name' => $this->order['receive_user'],
                'mobile' => $this->order['receive_mobile'],
                'printAddr' => $this->order['receive_address'],
            ],
            'sendMan' => [
                'name' => $this->order['send_user'],
                'mobile' => $this->order['send_mobile'],
                'printAddr' => $this->order['send_address'],
            ],
            'cargo' => $this->order['goods_full_name'],
            'count' => $this->order['goods_num'],
            'payType' => 'SHIPPER',
            'expType' => '标准快递',
            'remark' => $this->order['remark'],
            'tempid' => $this->tempIds[$this->order['express_code']],
            'siid' => $this->siid,
            'callBackUrl' => "https://casc168.dekichina.com/callback.php"
        ];
        $this->params = $params;
    }

    public function initPrintOldParams(){
        $params = [
            'taskId' => $this->order['task_id']
        ];
        $this->params = $params;
    }

    public function makeSign(){
        $param = json_encode($this->params);
        $t = $this->t = getMillisecond();
        $sign = strtoupper(md5($param . $t . $this->key . $this->secret));
        $this->sign = $sign;
    }

    /**
     * curl请求指定url (post)
     * @param $url
     * @param array $data
     * @return mixed
     */
    public function curlPost($url, $data = [])
    {
        $data = http_build_query($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // POST数据

        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);
        return $output;
    }

    /**
     * curl请求指定url (get)
     * @param $url
     * @param array $data
     * @return mixed
     */
    function curl($url, $data = [])
    {
        // 处理get数据
        if (!empty($data)) {
            $url = $url . '?' . http_build_query($data);
        }
        $header = array('Expect:');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $result = curl_exec($curl);
        curl_close($curl);
        var_dump($result);die;
        return $result;
    }

    function curl_get($url){

        $header = array(
            'Accept: application/json',
            'Expect:'
        );
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        // 超时设置，以毫秒为单位
        // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
        if (curl_error($curl)) {
            print "Error: " . curl_error($curl);die;
        } else {
            // 打印返回的内容
            var_dump($data);die;
            curl_close($curl);
        }
    }

    function http($url, $timeout = 30, $header = array())
    {
        if (! function_exists('curl_init')) {
            $this->error = 'server not install curl';
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if (! empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $data = curl_exec($ch);
        list ($header, $data) = explode("\r\n\r\n", $data);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = trim(array_pop($matches));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $data = curl_exec($ch);
        }

        if ($data == false) {
            curl_close($ch);
        }
        @curl_close($ch);
        var_dump($data);die;
        return $data;
    }

    public function getError(){
        return $this->error;
    }

}