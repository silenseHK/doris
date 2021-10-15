<?php


namespace app\common\library\aes;


class Aes
{

    protected $key = '1234567887654321';

    protected $iv = '1234567887654321';

    protected $method = 'AES-128-CBC';

//    //加密
//    public function aesEn($data){
//        return  base64_encode(openssl_encrypt($data, $this->method, $this->key,2, $this->iv));
//    }
//
//    //解密
//    public function aesDe($data){
////        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, base64_decode($data), MCRYPT_MODE_CBC, $this->iv);
//        return openssl_encrypt(base64_decode($data), $this->method, $this->key,2, $this->iv);
//    }

    //加密
    public function aesEn($data){
        if(is_array($data))$data = json_encode($data);
        return  base64_encode(openssl_encrypt($data, $this->method,$this->key, OPENSSL_RAW_DATA , $this->iv));
    }

    //解密
    public function aesDe($data){
        return openssl_decrypt(base64_decode($data),  $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }

}