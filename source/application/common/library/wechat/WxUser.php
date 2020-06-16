<?php

namespace app\common\library\wechat;

use app\common\library\wechat\bizdatacrypt\WXBizDataCrypt;

/**
 * 微信小程序用户管理类
 * Class WxUser
 * @package app\common\library\wechat
 */
class WxUser extends WxBase
{
    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        return $result;
    }

    /**
     * 解密获取用户的unionId
     * @param $sessionKey
     * @param $encryptedData
     * @param $iv
     * @return bool|mixed
     */
    public function unionId($sessionKey, $encryptedData, $iv){
        $pc = new WXBizDataCrypt($this->appId, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        print_r($data);die;
        if ($errCode == 0){
            return $data['unionId'];
        } else {
            $this->error = "获取unionID失败,错误码：{$errCode}";
            return false;
        }
    }

}